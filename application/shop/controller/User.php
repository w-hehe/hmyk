<?php

namespace app\shop\controller;

use think\Db;
use think\Session;

class User extends Base {

    //设置头像
    public function avatar() {

        if ($this->request->isPost()) {
            $upload = $this->upload();
            if ($upload['code'] == 0) {
                $this->error('上传失败');
            }
            $where = ['id' => $this->uid];
            $update = ['avatar' => $upload['url'], 'updatetime' => time()];
            $res = db::name('user')->where($where)->update($update);
            $data = ['url' => $upload['url']];
            if ($res) {
                $this->success('上传成功', $data);
            } else {
                $this->error('上传失败');
            }

        }

        $user = db::name('user')->where(['id' => $this->uid])->find();
        $avatar = empty($user['avatar']) ? $this->avatar : $user['avatar'];
        $this->assign([
            'avatar' => $avatar,
        ]);

        return view($this->template_path . "avatar.html");
    }

    //用户个人中心
    public function index() {
        Db::startTrans();

        $uid = $this->uid == null ? $this->tourist : $this->uid;

        $user = db::name('user')->where(['id' => $uid])->find();
        $avatar = empty($user['avatar']) ? $this->avatar : $user['avatar'];
        $order = [
            //待付款
            'dfk' => db::name('order')->where(['uid' => $uid, 'station_id' => $this->station_id, 'status' => -1])->count(),
            //代发货
            'dfh' => db::name('order')->where(['uid' => $uid, 'station_id' => $this->station_id, 'status' => 0])->count(),
            //待收货
            'dsh' => db::name('order')->where(['uid' => $uid, 'station_id' => $this->station_id, 'status' => 1])->count(),
        ];



        Db::commit();

        $this->assign([
            'user' => $user, 'avatar' => $avatar, 'footer_active' => 'user', 'order' => $order,
        ]);
        return view($this->template_path . "user.html");
    }

    //设置页面
    public function setting() {
        if($this->uid == null){
            $this->redirect(url("/user"));
        }
        Db::startTrans();
        $user = db::name('user')->where(['id' => $this->uid])->find();
        $alipay = db::name('user_alipay')->where(['uid' => $this->uid])->find();
        Db::commit();
        $email = empty($user['email']) ? '未绑定' : $user['email'];
        $nickname = empty($user['nickname']) ? '' : $user['nickname'];
        if ($user['gender'] === null) {
            $gender = '未知';
        } else {
            $gender = $user['gender'] == 0 ? '女' : '男';
        }
        $alipay_account = $alipay ? $alipay['account'] : '未绑定';
        $avatar = empty($user['avatar']) ? $this->avatar : $user['avatar'];
        $this->assign([
            'avatar' => $avatar, 'user' => $user, 'email' => $email, 'nickname' => $nickname, 'gender' => $gender, 'alipay_account' => $alipay_account,
        ]);
        return view($this->template_path . "setting.html");
    }

    /**
     * 绑定支付宝
     */
    public function alipay() {
        if ($this->request->isAjax()) {
            $post = $this->request->param();
            Db::startTrans();
            try {
                $res = db::name('user_alipay')->where(['uid' => $this->uid])->find();
                $data = [
                    'account' => $post['account'], 'name' => $post['name'], 'uid' => $this->uid,
                ];
                if ($res) {
                    $data['updatetime'] = time();
                    $res = db::name('user_alipay')->where(['uid' => $this->uid])->update($data);
                } else {
                    $data['createtime'] = time();
                    $res = db::name('user_alipay')->insert($data);
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($res) {
                $this->success('绑定成功');
            } else {
                $this->error('绑定失败');
            }
        }
        $alipay = db::name('user_alipay')->where(['uid' => $this->uid])->find();
        $this->assign([
            'alipay' => $alipay
        ]);
        return view($this->template_path . "alipay.html");
    }

    /**
     * 修改性别
     */
    public function gender() {
        if ($this->request->isAjax()) {
            $post = $this->request->param();
            $update = [
                'gender' => $post['gender'], 'updatetime' => time()
            ];
            $res = db::name('user')->where(['id' => $this->uid])->update($update);
            if ($res) {
                $this->success('设置成功');
            } else {
                $this->error('设置失败');
            }
        }
        $gender = db::name('user')->where(['id' => $this->uid])->value('gender');
        $this->assign([
            'gender' => $gender,
        ]);
        return view($this->template_path . "gender.html");
    }

    /**
     * 修改昵称
     */
    public function nickname() {
        if ($this->request->isAjax()) {
            $post = $this->request->param();
            Db::startTrans();
            try {
                $where = [
                    'id' => ['neq', $this->uid], 'nickname' => $post['nickname'],
                ];
                $res = db::name('user')->where($where)->find();
                if ($res) {
                    throw new \Exception("昵称已被他人使用");
                }
                $update = [
                    'nickname' => $post['nickname'], 'updatetime' => time(),
                ];
                $res = db::name('user')->where(['id' => $this->uid])->update($update);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($res) {
                $this->success("修改成功");
            } else {
                $this->error("修改失败");
            }
        }
        $nickname = db::name('user')->where(['id' => $this->uid])->value('nickname');
        $this->assign([
            'nickname' => $nickname
        ]);
        return view($this->template_path . "nickname.html");
    }

    //退出登录
    public function logout() {
        session::delete('user');
        $this->redirect(url('/'));
    }

    //绑定邮箱
    public function email() {
        if ($this->request->isAjax()) {
            $post = $this->request->param();
            if (!session::has('emailcode_set')) {
                $this->error("验证码错误");
            }
            $server_code = session::get('emailcode_set');
            if ($post['email'] != $server_code['email']) {
                $this->error("邮箱已更换，请重新获取验证码");
            }
            if ($post['code'] != $server_code['code']) {
                $this->error("验证码错误");
            }
            $where = [
                'id' => ['neq', $this->uid], 'email' => $post['email'],
            ];
            Db::startTrans();
            try {
                $user = db::name('user')->where($where)->find();
                if ($user) {
                    throw new \Exception("该邮箱已被其他帐号绑定");
                }
                $update = [
                    'email' => $post['email'], 'updatetime' => time(),
                ];
                $where = [
                    'id' => $this->uid,
                ];
                $res = db::name('user')->where($where)->update($update);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($res) {
                session::delete('emailcode_set');
                $this->success('绑定成功');
            } else {
                $this->error('绑定失败');
            }

        }

        $user = db::name('user')->where(['id' => $this->uid])->find();


        if (!empty($user['email'])) {
            $this->assign(['email' => $user['email']]);
        }
        return view($this->template_path . "email.html");
    }

}
