<?php

class TwigManager {
    protected static $twig;

    public static function getInstance() {
        if (!self::$twig) {
            $loader = new \Twig\Loader\FilesystemLoader(ROOT . DS . 'app' . DS . 'view');
            self::$twig = new \Twig\Environment($loader);
        }

        return self::$twig;
    }
}