<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Support as SupportModel;

/**
 * 客服帮助与公告
 */
class Support extends Api
{
    protected $noNeedLogin = ['lists', 'detail'];
    protected $noNeedRight = '*';

    /**
     * 列表
     *
     * @ApiParams (name="type", type="string", required=false, description="faq/notice")
     * @ApiParams (name="lang", type="string", required=false, description="ja/zh/en")
     */
    public function lists()
    {
        $type = $this->request->param('type');
        $lang = $this->request->param('lang');
        $query = SupportModel::where('status', 'normal');
        if ($type) {
            $query->where('type', $type);
        }
        if ($lang) {
            $query->where('lang', $lang);
        }
        $list = $query->order('weigh', 'desc')->select();
        $rows = [];
        foreach ($list as $item) {
            $rows[] = [
                'id'         => (int)$item['id'],
                'type'       => $item['type'],
                'title'      => $item['title'],
                'content'    => $item['content'],
                'lang'       => $item['lang'],
                'createtime' => (int)$item['createtime'],
            ];
        }
        $this->success('', $rows);
    }

    /**
     * 详情
     */
    public function detail()
    {
        $id = (int)$this->request->param('id');
        $row = SupportModel::get($id);
        if (!$row) {
            $this->error('内容不存在');
        }
        $this->success('', $row->toArray());
    }
}
