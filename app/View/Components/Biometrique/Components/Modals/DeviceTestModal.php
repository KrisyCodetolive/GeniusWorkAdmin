<?php

namespace App\View\Components\Biometrique\Components\Modals;

use Illuminate\View\Component;

class DeviceTestModal extends Component
{
    /**
     * L'appareil biométrique à tester.
     *
     * @var mixed|null
     */
    public $appareil;

    /**
     * Create a new component instance.
     *
     * @param mixed|null $appareil
     * @return void
     */
    public function __construct($appareil = null)
    {
        $this->appareil = $appareil;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.biometrique.components.modals.device-test-modal');
    }
}
