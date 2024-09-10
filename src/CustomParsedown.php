<?php

require 'Parsedown.php';

class CustomParsedown extends Parsedown
{
    protected function blockHeader($Line)
    {
        $block = parent::blockHeader($Line);

        if (isset($block)) {
            $text = $block['element']['text'];
            $id = $this->createId($text);
            $block['element']['attributes'] = ['id' => $id];
        }

        return $block;
    }

    private function createId($text)
    {
        // Convert the heading text to a slug for use as an ID
        $id = strtolower($text); // convert to lowercase
        $id = preg_replace('/[^\w]+/', '-', $id); // replace non-word characters with hyphens
        $id = trim($id, '-'); // trim leading/trailing hyphens
        return $id;
    }

    protected function inlineLink($Excerpt)
    {
        $link = parent::inlineLink($Excerpt);

        if (isset($link['element']['attributes']['href'])) {
            $href = $link['element']['attributes']['href'];
            if ($this->isExternalLink($href)) {
                $link['element']['attributes']['target'] = '_blank';
            }
        }

        return $link;
    }

    private function isExternalLink($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        if ($host && $host !== $_SERVER['HTTP_HOST']) {
            return true;
        }
        return false;
    }
}
