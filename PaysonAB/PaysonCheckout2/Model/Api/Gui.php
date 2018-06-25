<?php
namespace PaysonAB\PaysonCheckout2\Model\Api;

class Gui
{
    /**
 * @var string $colorScheme Color scheme of the checkout snippet("white", "black", "blue" (default), "red"). 
*/
    public $colorScheme;
    /**
 * @var string $locale Used to change the language shown in the checkout snippet ("se", "fi", "en" (default)). 
*/
    public $locale;
    /**
 * @var string $verfication  Can be used to add extra customer verfication ("bankid", "none" (default)). 
*/
    public $verfication;
    /**
 * @var bool $requestPhone  Can be used to require the user to fill in his phone number. 
*/
    public $requestPhone;
    /**
 * @var  string $countries 
*/
    public $countries;


    /**
     * @param string         $locale
     * @param string         $colorScheme
     * @param string         $verfication
     * @param null           $requestPhone
     * @param $allowedCountry
     * @return $this
     */
    public function guiInit($locale = "sv", $colorScheme = "gray", $verfication = "none", $requestPhone = null, $countries)
    {
        $this->colorScheme = $colorScheme;
        $this->locale = $locale;
        $this->verfication = $verfication;
        $this->requestPhone = $requestPhone;
        $this->countries = $countries;
        return $this;
    }

    /**
     * @param $data
     * @return Gui
     */
    public static function create($data)
    {
        $guiObject = new Gui();
        return $guiObject->guiInit($data->locale, $data->colorScheme, $data->verification, $data->requestPhone, $data->countries);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
