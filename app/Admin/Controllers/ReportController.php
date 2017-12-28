<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Statisticsdata;
use Carbon\Carbon;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Request $request)
    {
        return Admin::content(function (Content $content) use ($request){
            $content->header('数据报表');
            $content->description('');
            $content->body($this->grid($request));
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid($request)
    {
        return Admin::grid(Statisticsdata::class, function (Grid $grid) use ($request){
            $grid->disableCreation();
            $grid->disableActions();
            $grid->disableRowSelector();
            $grid->filter(function($filter){
//                $filter->useModal();
                $filter->between('created_at', '日期')->datetime();
            });

            if(!$request->has('created_at')){
//               $grid->model()->where('created_at', '>', Carbon::now()->toDateString());
                $grid->model()->orderBy('created_at','desc');
            }

//            $grid->id('ID')->sortable();
            $grid->advertisement()->id('广告ID');
            $grid->advertisement()->name('广告标题');
            $grid->channel()->id('渠道ID');
            $grid->channel()->name('渠道名称');
            $grid->click_count('点击数');
            $grid->conversion_count('转化数');
            $grid->total_cost('转化金额');
            $grid->created_at('日期');
//            $grid->updated_at();
        });
    }

}
