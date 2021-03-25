<?php declare(strict_types=1);

namespace JTL\Backend\Wizard;

use Illuminate\Support\Collection;
use JTL\Backend\Wizard\Steps\ErrorCode;
use JTL\Backend\Wizard\Steps\Error;
use JTL\Backend\Wizard\Steps\Step;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\L10n\GetText;
use JTL\Session\Backend;

/**
 * Class Controller
 * @package JTL\Backend\Wizard
 */
final class Controller
{
    /**
     * @var Collection
     */
    private $steps;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * Controller constructor.
     * @param DefaultFactory $factory
     * @param DbInterface $db
     * @param JTLCacheInterface $cache
     * @param GetText $getText
     */
    public function __construct(DefaultFactory $factory, DbInterface $db, JTLCacheInterface $cache, GetText $getText)
    {
        $getText->loadAdminLocale('pages/pluginverwaltung');

        $this->steps = $factory->getSteps();
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * @param array $post
     * @return array
     */
    public function answerQuestions(array $post): array
    {
        if (empty($post)) {
            return [];
        }
        $post = $this->serializeToArray($post);

        foreach ($this->getSteps() as $step) {
            foreach ($step->getQuestions() as $question) {
                /** @var QuestionInterface $question */
                $question->answerFromPost($post);
            }
        }

        return $this->finish();
    }

    /**
     * @return array
     */
    private function finish(): array
    {
        $errorMessages = [];
        /** @var Step $step*/
        foreach ($this->getSteps() as $step) {
            foreach ($step->getQuestions() as $question) {
                /** @var QuestionInterface $question */
                if (($errorCode = $question->save()) !== ErrorCode::OK) {
                    $step->addError(new Error($step->getID(), $question->getID(), $errorCode));
                }
            }
            $errorMessages = \array_merge($errorMessages, $step->getErrors()->toArray());
        }
        if (!$this->hasCriticalError()) {
            $this->db->update(
                'teinstellungen',
                'cName',
                'global_wizard_done',
                (object)['cWert' => 'Y']
            );
            $this->cache->flushAll();
            unset($_SESSION['wizard']);
        }

        return $errorMessages;
    }

    /**
     * @param array $post
     * @return array
     */
    public function validateStep(array $post): array
    {
        $post          = $this->serializeToArray($post);
        $errorMessages = [];
        /** @var Step $step*/
        foreach ($this->getSteps() as $step) {
            foreach ($step->getQuestions() as $question) {
                $idx = 'question-' . $question->getID();
                if (isset($post[$idx])) {
                    /** @var QuestionInterface $question */
                    $question->answerFromPost($post);
                    if (($errorCode = $question->validate()) !== ErrorCode::OK) {
                        $step->addError(new Error($step->getID(), $question->getID(), $errorCode));
                    }
                }
            }
            $errorMessages = \array_merge($errorMessages, $step->getErrors()->toArray());
        }
        Backend::set('wizard', \array_merge(Backend::get('wizard') ?? [], $post));

        return $errorMessages;
    }

    /**
     * @param array $post
     * @return array
     */
    public function serializeToArray(array $post): array
    {
        if (\is_array($post[0])) {
            $postTMP = [];
            foreach ($post as $postItem) {
                if (\mb_strpos($postItem['name'], '[]') !== false) {
                    $postTMP[\explode('[]', $postItem['name'])[0]][] = $postItem['value'];
                } else {
                    $postTMP[$postItem['name']] = $postItem['value'];
                }
            }
            $post = $postTMP;
        }

        return $post;
    }

    /**
     * @return Collection
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    /**
     * @param Collection $steps
     */
    public function setSteps(Collection $steps): void
    {
        $this->steps = $steps;
    }

    /**
     * @return bool
     */
    public function hasCriticalError(): bool
    {
        /** @var Step $step*/
        foreach ($this->getSteps() as $step) {
            if ($step->hasCriticalError()) {
                return true;
            }
        }

        return false;
    }
}
