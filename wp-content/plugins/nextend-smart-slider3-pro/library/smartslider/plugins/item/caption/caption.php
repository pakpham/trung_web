<?php
N2Loader::import('libraries.slider.slides.slide.item.itemFactoryAbstract', 'smartslider');
N2Loader::import('libraries.image.color');

class N2SSPluginItemFactoryCaption extends N2SSPluginItemFactoryAbstract {

    var $type = 'caption';

    protected $priority = 7;

    protected $layerProperties = array(
        "desktopportraitleft"  => 0,
        "desktopportraittop"   => 0,
        "desktopportraitwidth" => 200
    );

    protected $group = 'Content';

    private static $fontTitle = 1003;
    private static $font = 1003;

    protected $class = 'N2SSItemCaption';

    public function __construct() {
        $this->_title = n2_x('Caption', 'Slide item');
    }

    private static function initDefaultFont() {
        static $inited = false;
        if (!$inited) {
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-caption-font-title');
            if (is_array($res)) {
                self::$fontTitle = $res['value'];
            }
            if (is_numeric(self::$fontTitle)) {
                N2FontRenderer::preLoad(self::$fontTitle);
            }
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-caption-font');
            if (is_array($res)) {
                self::$font = $res['value'];
            }
            if (is_numeric(self::$font)) {
                N2FontRenderer::preLoad(self::$font);
            }
            $inited = true;
        }
    }

    public static function onSmartsliderDefaultSettings(&$settings) {
        self::initDefaultFont();
        $settings['font'][] = '<param name="item-caption-font-title" type="font" previewmode="paragraph" label="' . n2_('Item') . ' - ' . n2_('Caption') . ' - ' . n2_('Title') . '" default="' . self::$fontTitle . '" />';
        $settings['font'][] = '<param name="item-caption-font" type="font" previewmode="paragraph" label="' . n2_('Item') . ' - ' . n2_('Caption') . ' - ' . n2_('Description') . '" default="' . self::$font . '" />';
    }

    function getTemplate($slider) {

        $this->loadResources($slider);

        $html = N2Html::openTag("div", array(
            "id"    => '{uid}',
            "class" => "n2-ss-item-caption n2-ow"
        ));

        $html .= N2Html::openTag("a", array(
            "href"    => '#',
            "class"   => 'n2-ow',
            "onclick" => "return false;"
        ));

        $html .= N2Html::tag('div', array('class' => 'n2-ss-img-wrapper n2-ow'), N2Html::image('{image}', 'Image', array('class' => 'n2-ow')));

        $html .= N2Html::openTag("div", array(
            "class" => "n2-ss-item-caption-content n2-ow",
            "style" => "background: #{colorhex}; background: {colora};"
        ));

        $html .= N2Html::tag("div", array("class" => '{fonttitleclass} n2-div-h4 n2-ow'), "{content}");


        $html .= N2Html::tag("p", array("class" => '{fontclass} n2-ow'), "{description}");


        $html .= N2Html::closeTag("div");

        $html .= N2Html::scriptTemplate($this->getJs($slider->elementId, "{uid}"));

        $html .= N2Html::closeTag("a");

        $html .= N2Html::closeTag("div");

        return $html;
    }

    function getJs($sliderId, $id) {
        return 'new N2Classes.FrontendItemCaption(window["' . $sliderId . '"], "' . $id . '", "{mode}", "{direction}", {scale});';
    }

    private function loadResources($slider) {
        N2LESS::addFile(N2Filesystem::translate($this->getPath() . "/caption.n2less"), $slider->cacheId, array(
            "sliderid" => $slider->elementId
        ), NEXTEND_SMARTSLIDER_ASSETS . '/less' . NDS);
    }

    function getValues() {
        self::initDefaultFont();

        return array(
            'animation'      => 'Simple|*|left|*|0',
            'image'          => '$system$/images/placeholder/image.png',
            'alt'            => n2_('Image not available'),
            'link'           => '#|*|_self',
            'content'        => n2_('Caption'),
            'description'    => '',
            'fonttitle'      => self::$fontTitle,
            'font'           => self::$font,
            'color'          => '00000080',
            'image-optimize' => 1
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public function getFilled($slide, $data) {
        $data->set('image', $slide->fill($data->get('image', '')));
        $data->set('alt', $slide->fill($data->get('alt', '')));
        $data->set('content', $slide->fill($data->get('content', '')));
        $data->set('description', $slide->fill($data->get('description', '')));
        $data->set('link', $slide->fill($data->get('link', '#|*|')));

        return $data;
    }

    public function prepareExport($export, $data) {
        $export->addImage($data->get('image'));
        $export->addVisual($data->get('font'));
        $export->addVisual($data->get('fonttitle'));
        $export->addLightbox($data->get('link'));
    }

    public function prepareImport($import, $data) {
        $data->set('image', $import->fixImage($data->get('image')));
        $data->set('font', $import->fixSection($data->get('font')));
        $data->set('fonttitle', $import->fixSection($data->get('fonttitle')));
        $data->set('link', $import->fixLightbox($data->get('link')));

        return $data;
    }

    public function prepareSample($data) {
        $data->set('image', N2ImageHelper::fixed($data->get('image')));

        return $data;
    }

}

N2Plugin::addPlugin('ssitem', 'N2SSPluginItemFactoryCaption');

N2Pluggable::addAction('smartsliderDefault', 'N2SSPluginItemFactoryCaption::onSmartsliderDefaultSettings');
