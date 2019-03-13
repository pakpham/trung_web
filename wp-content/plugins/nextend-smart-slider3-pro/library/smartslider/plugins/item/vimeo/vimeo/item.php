<?php

N2Loader::import('libraries.slider.slides.slide.itemFactory', 'smartslider');

class N2SSItemVimeo extends N2SSItemAbstract {

    protected $type = 'vimeo';

    public function render() {
        $slide  = $this->layer->getSlide();
        $slider = $slide->getSlider();

        $this->data->set("vimeocode", preg_replace('/\D/', '', $slide->fill($this->data->get("vimeourl"))));

        $style = '';

        $hasImage  = 0;
        $image     = $this->data->get('image');
        $playImage = '';
        if (!empty($image)) {
            $style    = 'cursor:pointer; background: url(' . N2ImageHelper::fixed($image) . ') no-repeat 50% 50%; background-size: cover';
            $hasImage = 1;
            if ($this->data->get('playbutton', 1) != 0) {
                $playImage  = '<img class="n2-video-play n2-ow" alt="Play" style="';
                $playWidth  = intval($this->data->get('playbuttonwidth', '48'));
                $playHeight = intval($this->data->get('playbuttonheight', '48'));
                if ($playWidth != 0 && $playHeight != 0) {
                    $playImage .= 'width:' . $playWidth . 'px;';
                    $playImage .= 'height:' . $playHeight . 'px;';
                    $playImage .= 'margin-left:' . ($playWidth / -2) . 'px;';
                    $playImage .= 'margin-top:' . ($playHeight / -2) . 'px;';
                }
                $playImage .= '" src="';
                $playbuttonimage = $this->data->get('playbuttonimage', '');
                if (!empty($playbuttonimage)) {
                    $playImage .= N2ImageHelper::fixed($this->data->get('playbuttonimage', ''));
                } else {
                    $playImage .= N2ImageHelperAbstract::SVGToBase64('$ss$/images/play.svg');
                }
                $playImage .= '"/>';
            }
        }

        N2JS::addInline('window["' . $slider->elementId . '"].ready(function() {
                new N2Classes.FrontendItemVimeo(this, "' . $this->id . '", "' . $slider->elementId . '", ' . $this->data->toJSON() . ', ' . $hasImage . ', ' . $slide->fill($this->data->get('start', '0')) . ');
            });
        ');

        return N2Html::tag('div', array(
            'id'    => $this->id,
            'class' => 'n2-ow',
            'style' => 'position: absolute; top: 0; left: 0; width: 100%; height: 100%;' . $style
        ), $playImage);
    }

    public function _renderAdmin() {
        $slide  = $this->layer->getSlide();
        $slider = $slide->getSlider();

        return N2Html::tag('div', array(
            "class" => 'n2-ow',
            "style" => 'width: 100%; height: 100%; background: url(' . N2ImageHelper::fixed($this->data->getIfEmpty('image', '$system$/images/placeholder/video.png')) . ') no-repeat 50% 50%; background-size: cover;'
        ), '<img class="n2-video-play n2-ow" alt="" src="' . N2ImageHelperAbstract::SVGToBase64('$ss$/images/play.svg') . '"/>');
    }

    public function needSize() {
        return true;
    }
}