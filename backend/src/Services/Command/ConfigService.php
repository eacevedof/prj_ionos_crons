<?php
namespace App\Services\Command;

use function App\Functions\get_config;
use App\Component\ColorComponent as Color;

class ConfigService extends ACommandService
{
    private function _get_data(string $param): string
    {
        $data = get_config($param);
        return print_r($data,1);
    }

    public function run(): void
    {
        //$this->_debug();
        $param = $this->_get_request(2) ?? "services";

        $data = $this->_get_data($param);
        echo Color::text($param."\n",Color::BLUE);
        echo $data;
    }
}