<?php
Class Traduccio {
    /**
     * @var string
     */
    private $idioma;

    /**
     * @var array
     */
    private $locales;

    public function __construct($idioma, $locales) {
        $this->idioma = $idioma;
        
        if ($locales) {
            foreach($locales as $locale) {
                $f = $locale."_".$idioma.".json";
                if (file_exists($f)) {
                    $this->locales[] = json_decode(file_get_contents($f), true);
                }
            }
        }
    }

    public function get($key) {
        $ret = "";
        if ($this->locales) {
            foreach($this->locales as $locale) {
                if (array_key_exists($key, $locale)) {
                    $ret = $locale[$key];
                    break;
                }
            }
        }
        return $ret;
    }
}