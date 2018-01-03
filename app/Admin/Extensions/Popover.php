<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/3
 * Time: 16:17
 */
namespace App\Admin\Extensions;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Displayers\AbstractDisplayer;

class Popover extends AbstractDisplayer
{
    public function display($placement = 'left')
    {
        Admin::script("$('[data-toggle=\"popover\"]').popover({html : true})");
        return <<<EOT
<button type="button"
    class="btn btn-secondary"
    title="点击汇报链接列表"
    data-container="body"
    data-toggle="popover"
    data-placement="$placement"
    data-content="{$this->value}"
    >
  弹出提示
</button>

EOT;

    }
}