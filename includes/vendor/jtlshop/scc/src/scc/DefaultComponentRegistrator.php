<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace scc;

use scc\components\Accordion;
use scc\components\Alert;
use scc\components\Badge;
use scc\components\Breadcrumb;
use scc\components\BreadcrumbItem;
use scc\components\Button;
use scc\components\ButtonGroup;
use scc\components\ButtonToolbar;
use scc\components\Card;
use scc\components\CardBody;
use scc\components\CardFooter;
use scc\components\CardGroup;
use scc\components\CardHeader;
use scc\components\CardImg;
use scc\components\Carousel;
use scc\components\CarouselSlide;
use scc\components\Checkbox;
use scc\components\CheckboxGroup;
use scc\components\Clearfix;
use scc\components\Col;
use scc\components\Collapse;
use scc\components\Container;
use scc\components\CSRFToken;
use scc\components\DropDown;
use scc\components\DropDownDivider;
use scc\components\DropDownItem;
use scc\components\Embed;
use scc\components\Form;
use scc\components\FormGroup;
use scc\components\FormRow;
use scc\components\Image;
use scc\components\Input;
use scc\components\InputFile;
use scc\components\InputGroup;
use scc\components\InputGroupAddon;
use scc\components\InputGroupAppend;
use scc\components\InputGroupPrepend;
use scc\components\InputGroupText;
use scc\components\Jumbotron;
use scc\components\Link;
use scc\components\ListGroup;
use scc\components\ListGroupItem;
use scc\components\Media;
use scc\components\MediaAside;
use scc\components\MediaBody;
use scc\components\Modal;
use scc\components\Nav;
use scc\components\Navbar;
use scc\components\NavbarBrand;
use scc\components\NavbarNav;
use scc\components\NavbarToggle;
use scc\components\NavForm;
use scc\components\NavItem;
use scc\components\NavItemDropdown;
use scc\components\NavText;
use scc\components\Pagination;
use scc\components\Progress;
use scc\components\Radio;
use scc\components\RadioGroup;
use scc\components\Row;
use scc\components\Select;
use scc\components\Tab;
use scc\components\Table;
use scc\components\Tabs;
use scc\components\Textarea;

/**
 * Class DefaultComponentRegistrator
 * @package scc
 */
class DefaultComponentRegistrator
{
    /**
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * @var ComponentInterface[]
     */
    protected $components = [];

    /**
     * DefaultComponentRegistrator constructor.
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     *
     */
    public function registerComponents(): void
    {
        $this->components[] = new Accordion();
        $this->components[] = new Card();
        $this->components[] = new Input();
        $this->components[] = new Link();
        $this->components[] = new Button();
        $this->components[] = new ListGroup();
        $this->components[] = new ListGroupItem();
        $this->components[] = new Modal();
        $this->components[] = new Tabs();
        $this->components[] = new Tab();
        $this->components[] = new Badge();
        $this->components[] = new Image();
        $this->components[] = new Alert();
        $this->components[] = new Jumbotron();
        $this->components[] = new DropDown();
        $this->components[] = new DropDownItem();
        $this->components[] = new DropDownDivider();
        $this->components[] = new ButtonGroup();
        $this->components[] = new ButtonToolbar();
        $this->components[] = new Carousel();
        $this->components[] = new CarouselSlide();
        $this->components[] = new MediaAside();
        $this->components[] = new MediaBody();
        $this->components[] = new Media();
        $this->components[] = new Checkbox();
        $this->components[] = new CheckboxGroup();
        $this->components[] = new Radio();
        $this->components[] = new RadioGroup();
        $this->components[] = new FormGroup();
        $this->components[] = new Select();
        $this->components[] = new Textarea();
        $this->components[] = new InputFile();
        $this->components[] = new Form();
        $this->components[] = new InputGroup();
        $this->components[] = new InputGroupAddon();
        $this->components[] = new InputGroupAppend();
        $this->components[] = new InputGroupPrepend();
        $this->components[] = new InputGroupText();
        $this->components[] = new Container();
        $this->components[] = new Row();
        $this->components[] = new Col();
        $this->components[] = new FormRow();
        $this->components[] = new Pagination();
        $this->components[] = new Embed();
        $this->components[] = new CardGroup();
        $this->components[] = new CardBody();
        $this->components[] = new CardHeader();
        $this->components[] = new CardFooter();
        $this->components[] = new CardImg();
        $this->components[] = new Nav();
        $this->components[] = new NavForm();
        $this->components[] = new NavText();
        $this->components[] = new NavItem();
        $this->components[] = new NavItemDropdown();
        $this->components[] = new Navbar();
        $this->components[] = new NavbarNav();
        $this->components[] = new NavbarBrand();
        $this->components[] = new NavbarToggle();
        $this->components[] = new Collapse();
        $this->components[] = new Progress();
        $this->components[] = new Breadcrumb();
        $this->components[] = new BreadcrumbItem();
        $this->components[] = new CSRFToken();
        $this->components[] = new Table();
        $this->components[] = new Clearfix();

        foreach ($this->components as $component) {
            $component->getRenderer()->preset();
            $this->renderer->registerComponent($component);
        }
    }
}
