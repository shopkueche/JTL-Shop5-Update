<?php declare(strict_types=1);
/** PHP Implementation of XTEA (www.php-einfach.de)
 *
 * XTEA was designed in 1997 by David Wheeler and Roger Needham
 * of the Cambridge Computer Laboratory.
 * It is not subject to any patents.
 *
 * It is a 64-bit Feistel cipher, consisting of 64 rounds.
 * XTA has a key length of 128 bits.
 *
 *
 * ***********************
 * Diese Implementierung darf frei verwendet werden, der Autor uebernimmt keine
 * Haftung fuer die Richtigkeit, Fehlerfreiheit oder die Funktionsfaehigkeit dieses Scripts.
 * Benutzung auf eigene Gefahr.
 *
 * Ueber einen Link auf www.php-einfach.de wuerden wir uns freuen.
 *
 * ************************
 * Usage:
 * <?php
 *
 * $xtea = new JTL\XTEA("secret Key");
 * $cipher = $xtea->Encrypt("Hello World"); //Encrypts 'Hello World'
 * $plain = $xtea->Decrypt($cipher); //Decrypts the cipher text
 *
 * echo $plain;
 */

namespace JTL\xtea;

/**
 * Class XTEA
 */
class XTEA
{
    /**
     * @var string|array
     */
    public $key;

    /**
     * @var int
     * CBC or ECB Mode - normaly, CBC Mode would be the right choice
     */
    public $cbc = 1;

    /**
     * XTEA constructor.
     * @param string|array $key
     */
    public function __construct($key)
    {
        $this->key_setup($key);
    }

    /**
     * @param string $plain
     * @return string
     */
    public function encrypt(string $plain): string
    {
        $cipher = [];
        $n      = \mb_strlen($plain);
        if ($n % 8 !== 0) {
            $lng = ($n + (8 - ($n % 8)));
        } else {
            $lng = 0;
        }
        $text = $this->str2long(\str_pad($plain, $lng, ' '));
        // Initialization vector: IV
        if ($this->cbc === 1) {
            $cipher[0][0] = \time();
            $cipher[0][1] = (double)\microtime() * 1000000;
        }

        $a   = 1;
        $cnt = \count($text);
        for ($i = 0; $i < $cnt; $i += 2) {
            if ($this->cbc === 1) {
                //$text mit letztem Geheimtext XOR Verknuepfen
                //$text is XORed with the previous ciphertext
                $text[$i]     ^= $cipher[$a - 1][0];
                $text[$i + 1] ^= $cipher[$a - 1][1];
            }

            $cipher[] = $this->block_encrypt($text[$i], $text[$i + 1]);
            $a++;
        }

        $output = '';
        $cnt    = \count($cipher);
        for ($i = 0; $i < $cnt; $i++) {
            $output .= $this->long2str($cipher[$i][0]);
            $output .= $this->long2str($cipher[$i][1]);
        }

        return \base64_encode($output);
    }

    /**
     * @param string $text
     * @return string
     */
    public function decrypt(string $text): string
    {
        $plain  = [];
        $cipher = $this->str2long(\base64_decode($text));
        $cnt    = \count($cipher);
        for ($i = $this->cbc === 1 ? 2 : 0; $i < $cnt; $i += 2) {
            $return = $this->block_decrypt($cipher[$i], $cipher[$i + 1]);
            // XORed $return with the previous ciphertext
            if ($this->cbc === 1) {
                $plain[] = [$return[0] ^ $cipher[$i - 2], $return[1] ^ $cipher[$i - 1]];
            } else {//EBC Mode
                $plain[] = $return;
            }
        }
        $output = '';
        $cnt    = \count($plain);
        for ($i = 0; $i < $cnt; $i++) {
            $output .= $this->long2str($plain[$i][0]);
            $output .= $this->long2str($plain[$i][1]);
        }

        return $output;
    }

    /**
     * @param string|array $key
     */
    public function key_setup($key): void
    {
        if (\is_array($key)) {
            $this->key = $key;
        } elseif (isset($key) && !empty($key)) {
            $this->key = $this->str2long(\str_pad($key, 16, $key));
        } else {
            $this->key = [0, 0, 0, 0];
        }
    }

    /**
     * @param int $length
     */
    public function benchmark(int $length = 1000)
    {
        $string = \str_pad('', $length, 'text');
        $xtea   = new self('key');
        $start  = \time() + (double)\microtime();
        $xtea->encrypt($string);
        $end = \time() + (double)\microtime();

        echo 'Encrypting ' . $length . ' bytes: ' . \round(
                $end - $start,
                2
            ) . ' seconds (' . \round($length / ($end - $start), 2) . ' bytes/second)<br>';
    }

    /**
     * verify the correct implementation of the blowfish algorithm
     *
     * @return bool
     */
    public function check_implementation(): bool
    {
        $xtea    = new self('');
        $vectors = [
            [
                [0x00000000, 0x00000000, 0x00000000, 0x00000000],
                [0x41414141, 0x41414141],
                [0xed23375a, 0x821a8c2d]
            ],
            [
                [0x00010203, 0x04050607, 0x08090a0b, 0x0c0d0e0f],
                [0x41424344, 0x45464748],
                [0x497df3d0, 0x72612cb5]
            ]
        ];
        // Correct implementation?
        $correct = true;
        // Test vectors, see http://www.schneier.com/code/vectors.txt
        foreach ($vectors as $vector) {
            $key    = $vector[0];
            $cipher = $vector[2];

            $xtea->key_setup($key);
            $return = $xtea->block_encrypt($vector[1][0], $vector[1][1]);

            if ((int)$return[0] !== (int)$cipher[0] || (int)$return[1] !== (int)$cipher[1]) {
                $correct = false;
            }
        }

        return $correct;
    }

    /**
     * @param $y
     * @param $z
     * @return array
     */
    public function block_encrypt($y, $z)
    {
        $sum   = 0;
        $delta = 0x9e3779b9;
        for ($i = 0; $i < 32; $i++) {
            $y = $this->add(
                $y,
                $this->add($z << 4 ^ $this->rshift($z, 5), $z) ^
                $this->add($sum, $this->key[$sum & 3])
            );

            $sum = $this->add($sum, $delta);
            $z   = $this->add(
                $z,
                $this->add($y << 4 ^ $this->rshift($y, 5), $y) ^
                $this->add($sum, $this->key[$this->rshift($sum, 11) & 3])
            );
        }
        $v[0] = $y;
        $v[1] = $z;

        return [$y, $z];
    }

    /**
     * @param $y
     * @param $z
     * @return array
     */
    private function block_decrypt($y, $z): array
    {
        $delta = 0x9e3779b9;
        $sum   = 0xC6EF3720;
        for ($i = 0; $i < 32; $i++) {
            $z   = $this->add(
                $z,
                -($this->add($y << 4 ^ $this->rshift($y, 5), $y) ^
                    $this->add($sum, $this->key[$this->rshift($sum, 11) & 3]))
            );
            $sum = $this->add($sum, -$delta);
            $y   = $this->add(
                $y,
                -($this->add($z << 4 ^ $this->rshift($z, 5), $z) ^
                    $this->add($sum, $this->key[$sum & 3]))
            );
        }

        return [$y, $z];
    }

    /**
     * @param $integer
     * @param $n
     * @return float|int
     */
    private function rshift($integer, $n)
    {
        // convert to 32 bits
        if (0xffffffff < $integer || -0xffffffff > $integer) {
            $integer = \fmod($integer, 0xffffffff + 1);
        }
        // convert to unsigned integer
        if (0x7fffffff < $integer) {
            $integer -= 0xffffffff + 1.0;
        } elseif (-0x80000000 > $integer) {
            $integer += 0xffffffff + 1.0;
        }
        // do right shift
        if (0 > $integer) {
            $integer &= 0x7fffffff;     // remove sign bit before shift
            $integer >>= $n;            // right shift
            $integer |= 1 << (31 - $n); // set shifted sign bit
        } else {
            $integer >>= $n;            // use normal right shift
        }

        return $integer;
    }

    /**
     * @param $i1
     * @param $i2
     * @return float|mixed
     */
    private function add($i1, $i2)
    {
        $result = 0.0;
        foreach (\func_get_args() as $value) {
            // remove sign if necessary
            if (0.0 > $value) {
                $value -= 1.0 + 0xffffffff;
            }
            $result += $value;
        }
        // convert to 32 bits
        if (0xffffffff < $result || -0xffffffff > $result) {
            $result = \fmod($result, 0xffffffff + 1);
        }
        // convert to signed integer
        if (0x7fffffff < $result) {
            $result -= 0xffffffff + 1.0;
        } elseif (-0x80000000 > $result) {
            $result += 0xffffffff + 1.0;
        }

        return $result;
    }


    /**
     * Covert a string into longinteger
     *
     * @param $data
     * @return array
     */
    private function str2long($data)
    {
        $tmp  = \unpack('N*', $data);
        $long = [];
        $j    = 0;
        foreach ($tmp as $value) {
            $long[$j++] = $value;
        }

        return $long;
    }

    /**
     * Convert a longinteger into a string
     *
     * @param $l
     * @return false|string
     */
    private function long2str($l)
    {
        return \pack('N', $l);
    }
}
