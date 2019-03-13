<?php

N2Loader::import('libraries.slider.slides.slide.itemFactory', 'smartslider');

class N2SSItemButton extends N2SSItemAbstract {

    protected $type = 'button';

    public function render() {
        return $this->getHtml();
    }

    public function _renderAdmin() {
        return $this->getHtml();
    }

    private function getHtml() {
        $slide  = $this->layer->getSlide();
        $slider = $slide->getSlider();

        $font = N2FontRenderer::render($this->data->get('font'), 'link', $slider->elementId, 'div#' . $slider->elementId . ' ', $slider->fontSize);

        $html = N2Html::openTag("div", array(
            "class" => "nextend-smartslider-button-container n2-ow {$font}",
            "style" => "cursor: pointer; display:" . ($this->data->get('fullwidth', 0) ? 'block' : 'inline-block') . ";" . ($this->data->get('nowrap', 1) ? 'white-space:nowrap;' : '')
        ));

        $style = N2StyleRenderer::render($this->data->get('style'), 'heading', $slider->elementId, 'div#' . $slider->elementId . ' ');

        $html .= $this->getLink($slide->fill($this->data->get("content")), array(
            "style" => "display:" . ($this->data->get('fullwidth', 0) ? 'block' : 'inline-block') . ";",
            "class" => "{$style} n2-ow {$this->data->get('class', '')}"
        ), true);

        $html .= N2Html::closeTag("div");

        return $html;
    }
}