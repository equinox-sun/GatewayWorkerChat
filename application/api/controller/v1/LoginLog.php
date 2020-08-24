<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2020/3/16 9:26
// +----------------------------------------------------------------------
// | TITLE: 后台登录日志
// +----------------------------------------------------------------------

namespace app\api\controller\v1;

use app\api\controller\Api;


class LoginLog extends Api
{
    public function loginLogList()
    {
        $json = $this->request->param();
        $data = json_decode($json['data'],true);
        //数据验证
        $result = $this->validate($data, [
            'page_no' => 'require|integer|>:0',
            'page_size' => 'require|integer|>:0',
        ]);
        if ($result !== true) {
            $this->returnmsg(402, $result);
        }

        $params['page_no'] = empty($data['page_no']) ? 1 : intval($data['page_no']);
        $params['page_size'] = empty($data['page_size']) ? 20 : intval($data['page_size']);

        $this->result(\app\api\model\LoginLog::loginList($params),'200','获取成功');
    }

}