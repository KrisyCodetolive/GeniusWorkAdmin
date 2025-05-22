<?php

namespace App\View\Components\Biometrique;

use Illuminate\View\Component;

class DeviceForm extends Component
{
    /**
     * Les sites disponibles pour l'appareil biométrique.
     *
     * @var array
     */
    public $sites;

    /**
     * L'action du formulaire.
     *
     * @var string
     */
    public $action;

    /**
     * La méthode HTTP du formulaire.
     *
     * @var string
     */
    public $method;

    /**
     * L'appareil biométrique (pour l'édition).
     *
     * @var mixed|null
     */
    public $appareil;

    /**
     * Create a new component instance.
     *
     * @param array $sites
     * @param string $action
     * @param string $method
     * @param mixed|null $appareil
     * @return void
     */
    public function __construct($sites, $action, $method = 'POST', $appareil = null)
    {
        $this->sites = $sites;
        $this->action = $action;
        $this->method = $method;
        $this->appareil = $appareil;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.biometrique.forms.device-form');
    }
}
