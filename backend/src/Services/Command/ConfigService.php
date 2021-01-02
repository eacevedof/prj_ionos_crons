<?php
namespace App\Services\Command;

use function App\Functions\get_config;

class ConfigService extends ACommandService
{
    private function _get_data(string $param): string
    {
        $data = get_config($param);
        return print_r($data,1);
    }

    public function run(): void
    {
        $param = $this->_get_request(3) ?? "services";
        $data = $this->_get_request($param);
        echo $data;
    }
}