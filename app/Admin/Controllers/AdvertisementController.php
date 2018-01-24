<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\AdvertisementRepository;
use App\Admin\Repositories\ChannelRepository;
use App\Admin\Requests\StoreAdvertisementRequest;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;

class AdvertisementController extends Controller
{
    use ModelForm;

    protected $advertisementRepository;
    protected $channelRepository;

    public function __construct(AdvertisementRepository $advertisementRepository,ChannelRepository $channelRepository)
    {
        $this->advertisementRepository = $advertisementRepository;
        $this->channelRepository = $channelRepository;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('广告列表');
            $content->description('');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('编辑广告');
            $content->description('');
            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('新增广告');
            $content->description('');
            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid($this->advertisementRepository->getSelfModelClassName(), function (Grid $grid) {
            $grid->model()->where('is_delete', 'F');
            $grid->id('ID')->sortable();
            $grid->column('name','广告名称');
            $grid->column('uuid','推广ID');
            $grid->column('track_type','广告跟踪类型');
//            $grid->column('loading_page','落地页');
//            $grid->channels('投放渠道')->display(function ($channels) {
//                $channels = array_map(function ($channel) {
//                    return "<span class='label label-success'>{$channel['name']}</span>";
//                }, $channels);
//                return join('&nbsp;', $channels);
//            })->popover('bottom');

            $grid->channels('投放渠道&点击汇报链接')->display(function ($channels) {
                $channels = array_map(function ($channel) {
                    $url = "";
                    switch ($channel['type']){
                        case 'hotmobi':
                                $url = env('API_URL').'click/'.$this->uuid.'/'.$channel['token'].'/to?idfa={idfa}&ip={ip}&useragent={useragent}&clicktime={clicktime}&wxidentify={wxidentify}&clickid={clickid}';
                            break;
                        case 'dotinapp':
                            $url = env('API_URL').'click/'.$this->uuid.'/'.$channel['token'].'/to?appid=id'. $this->id .'&pid='. $channel['name'] .'&idfa={idfa}&ip={ip}&ua={ua}&timestamp={timestamp}&clickid={clickid}';
                            break;
                        default:
                            break;
                    }
                    return  "<span class='label label-success'>{$channel['name']} : </span>".htmlentities($url);
                }, $channels);
                return join('<br/>', $channels);
            });
            $grid->created_at('创建时间');
            $grid->updated_at('最后更新时间');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form($this->advertisementRepository->getSelfModelClassName(), function (Form $form) {
            $form->display('id', 'ID');
            // 添加提交验证规则
            $form->text('name', '广告标题');
            $form->select('track_type', '广告跟踪类型')->options([
                'talking_data' => 'talking data',
                'paipaidai' => '拍拍贷',
            ]);
            $form->text('loading_page', '落地页');
            $form->text('click_track_url', '广告点击上报地址');
//            $form->text('source', '广告来源');
//            $form->text('source_offer_id', '广告来源id');
//            $form->text('payout', '广告单价');
//            $form->select('payout_type', '广告计费类型')->options([
//                'CPC' => 'CPC',
//                'CPI' => 'CPI',
//                'CPA' => 'CPA',
//            ]);
            $form->multipleSelect('channels','投放渠道')->options($this->channelRepository->getAllDataPluckNameAndId());
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '最后更新时间');
        });
    }

    public function store(StoreAdvertisementRequest $request)
    {
        $data = [
            'name' => $request->get('name'),
            'track_type' => $request->get('track_type'),
            'loading_page'  => $request->get('loading_page'),
            'click_track_url' => $request->get('click_track_url'),
//            'source' => $request->get('source'),
//            'source_offer_id' => $request->get('source_offer_id'),
//            'payout' => $request->get('payout'),
//            'payout_type' => $request->get('payout_type'),
            'add_user_id' => Admin::user()->id,
            'update_user_id' => Admin::user()->id,
            'uuid' => Uuid::generate(),
        ];

        $channels = $request->get('channels');

        $advertisement = $this->advertisementRepository->create($data);

        if(count($channels) > 0){
            $advertisement->channels()->attach(array_filter($channels));
        }

        return redirect(route('advertisements.index'));
    }

    public function update(StoreAdvertisementRequest $request,$id)
    {
        $advertisement = $this->advertisementRepository->byId($id);

        if($advertisement){
            $data = [
                'name' => $request->get('name'),
                'track_type' => $request->get('track_type'),
                'loading_page'  => $request->get('loading_page'),
                'click_track_url' => $request->get('click_track_url'),
//                'source' => $request->get('source'),
//                'source_offer_id' => $request->get('source_offer_id'),
//                'payout' => $request->get('payout'),
//                'payout_type' => $request->get('payout_type'),
                'update_user_id' => Admin::user()->id,
            ];
            $advertisement->update($data);
            $channels = array_filter($request->get('channels'));
            $advertisement->channels()->sync($channels);
            return redirect(route('advertisements.index'));
        }
        return back();
    }

    public function destroy(Request $request,$ids)
    {
        $arrId = explode(',',$ids);
        $data = [
            'is_delete' => 'T',
            'update_user_id' => Admin::user()->id,
        ];
        $flag = $this->advertisementRepository->batchDelete($arrId,$data);
        if($flag){
            return [
                'status' => 1,
                'message' => '删除成功'
            ];
        }else{
            return [
                'status' => 0,
                'message' => '删除失败'
            ];
        }
    }
}
