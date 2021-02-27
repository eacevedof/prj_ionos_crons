<?php
namespace App\Services\Command;

use App\Factories\Db as db;

class ChalantestFixService extends ACommandService
{
    /**
     * @var \App\Component\QueryComponent
     */
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = db::get("elchalanwp-test");
    }

    private function _update_wpch_posts()
    {
        $sql = "UPDATE wpch_posts SET post_content = REPLACE(post_content, 'www.elchalanaruba.com', 'test.elchalanaruba.com')";
        $this->db->exec($sql);
        $r = $this->db->exec($sql);
        $this->logpr($r,"_update_wpch_posts 1 affected");

        $sql = "UPDATE wpch_posts SET post_content = REPLACE(post_content, '/elchalanaruba.com', '/test.elchalanaruba.com')";
        $this->db->exec($sql);
        $r = $this->db->exec($sql);
        $this->logpr($r,"_update_wpch_posts 2 affected");
    }

    private function _update_wpch_options()
    {
        $sql = "
        UPDATE wpch_options SET option_value = REPLACE(option_value, '/elchalanaruba.com', '/test.elchalanaruba.com');
        ";
        $r = $this->db->exec($sql);
        $this->logpr($r,"_update_wpch_options 1 affected");

        $sql = "
        UPDATE wpch_options SET option_value = REPLACE(option_value, 'www.elchalanaruba.com', 'test.elchalanaruba.com');
        ";
        $r = $this->db->exec($sql);
        $this->logpr($r,"_update_wpch_options 2 affected");
    }

    public function run()
    {
        $this->_update_wpch_options();
        $this->_update_wpch_posts();
    }
}