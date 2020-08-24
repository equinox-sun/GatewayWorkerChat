<?php
// +----------------------------------------------------------------------
// | When work is a pleasure, life is a joy!
// +----------------------------------------------------------------------
// | User: Song   | Time:2020/4/27 10:40
// +----------------------------------------------------------------------
// | TITLE: api接口返回英文切换
// +----------------------------------------------------------------------
// Api返回信息错误提示
return [
    //公共
    'add_success'                                 => 'Added successfully', //添加成功
    'add_fail'                                    => 'Failed to add', //添加失败
    'success_del'                                 => 'Deleted successfully', //删除成功
    'fail_del'                                    => 'Failed to delete', //删除失败
    'success_modified'                            => 'Successfully modified', //修改成功
    'fail_edit'                                   => 'Failed to edit', //修改失败
    'data_success'                                => 'Got data successfully', //获取数据成功
    'data_failed'                                 => 'Failed to get data', //获取数据失败
    'operation_success'                         => 'Successful operation',   //操作成功
    'operation_failed'                          => 'Operation failed',      //操作失败
    'quantity_empty'                 => 'Quantity must be filled in ', //'数量不能为空',
    'quantity_number'               => 'Quantity must be a number', //'数量必须是数字',
    'quantity_than_0'               => 'Quantity must be greater than 0', //'数量必须大于0',
    'must_array'            =>  'Must be an array',   //必须是数组
    'id_empty'                  => 'ID must be filled in', //'ID不能为空',

    'id_than_0'                  => 'ID  must  be  greater  than  0', //'ID不能必须大于0',

    //验证码
    'verify_code_empty'           => 'Verification code must be filled in',  //验证码不能为空
    'verify_code_error'            => 'Verification code is wrong',  //验证码错误

    //购物车
    'bom_empty'                     => 'bom must be filled in ', //'bom不能为空',
    'bom_array'                     => 'bom must be an array', //'bom必须是数组',
    'add_cart_success'              => 'Add to shopping cart successfully!',  //添加购物车成功
    'goods_abnormality'              => 'Product is abnormal !',    //产品异常
    'ladder_price_anomalies'         => 'Tiered prices are abnormal!', //阶梯价格异常
    'add_cart_failed'                    => 'Failed to add to shopping cart!', //添加购物车失败
    'cart_id_empty'                  => 'Shopping cart ID must be filled in', //'购物车ID不能为空',
    'cart_than_0'                   => 'Shopping cart ID must be greater than 0', //'购物车ID必须大于0',
    'cart_id_array'                   => 'Shopping cart ID must be an array', //'购物车ID必须大于0',
    'product_is_not_cart'           => 'The current product is not in the shopping cart', //当前产品不在购物车中

    //帮助分类/文章
    'article_id_empty'              => 'Article ID must be filled in',  //文章ID不能为空

    //登录、注册
    'username_empty'         => 'Username must be filled in',      //用户名不能为空
    'email_empty'             => 'Mailbox must be filled in',      //邮箱不能为空
    'must_email'             => 'Username must be a mailbox',      //用户名必须是邮箱
    'mailbox_exists'              =>  'Mailbox already exists',   //  邮箱已存在
    'mailbox_not_exists'              =>  'Mailbox does not exist',   //  邮箱不存在
    'new_email'     =>  'Please fill in the correct new email address',     //请填写正确的新邮箱地址
    'pwd_empty'              => 'Password must be filled in',      //密码不能为空
    'pwd_length'            => 'Password length does not meet requirements',      //密码长度不符合要求
    'confirm_pad'           => 'Confirmation password must be filled in',      //确认密码不能为空
    'pwd_confirm_pad'                  =>  'Password and confirmation password are inconsistent',   //密码和确认密码不一致
    'account_error'         => 'Account number is incorrect',     //帐号不正确
    'pwd_correct'             => 'Correct password',     //密码正确
    'pwd_error'             => 'Password is incorrect',     //密码不正确
    'success_send_email'              =>  'Successful! Verification email has been sent to', //成功！验证电子邮件已发送至
    'repeatedly_email'              =>  'Please do not send emails repeatedly',  //请不要重复发送电子邮件
    'send_email'              =>  'Verification email has been sent', //已发送验证电子邮件
    'over_24_hours'      =>  'Verification code is over 24 hours and has expired',  //验证码超过24小时，已过期
    'code_error'      =>  'Verification code is incorrect, please reactivate!',  //验证码错误，请重新激活！
    'activate_account'          => 'Account is not activated',     //帐号未激活
    'activate_account_send'          => 'Account is not activated, email has been sent, please activate account',  //账号未激活，已发送邮件，请激活账户
    'login_success'             => 'Login succeeded',     //登录成功
    'change_pwd'                =>'You have requested to change your account password, the verification code is',//您已请求更改帐户密码，验证码为
    'token_empty'               =>  'token empty',//没有传token
    'token_error'               =>  'Access_token expired or error',
    'logout_success'                          =>  'Logout/Exit succeeded',  //注销/退出成功
    'logout_failed'                          =>  'Logout/Exit failed',  //注销/退出失败
    'register_success'                            => 'Registration succeeded', //注册成功
    'register_fail'                               => 'Registration failed', //注册失败
    'reset_pwd_success'             => 'Password reset succeeded',       //重置密码成功
    'reset_pwd_fail'             => 'Password reset failed',       //重置密码失败
    'email_content'         =>'You have changed your email account, please log in to the email and click the "Activate" link to complete',//您已经更改邮箱账号，请登录电子邮件，单击“激活”链接以完成
    'edit_mailbox_success'          =>  'Successfully modify the mailbox',  //修改邮箱成功
    'edit_mailbox_fail'          =>  'Failed to modify mailbox',  //修改邮箱失败
    'user_not' => 'User does not exist',   //用户不存在
    'verify_pass' => 'Password is correct, verified',   //密码正确，验证通过
    'verify_failed' =>  'Incorrect password, verification failed',  //密码不正确，验证失败
    'old_pwd' => 'Original password must be filled in',    //原始密码不能为空
    'new_pwd' => 'New password must be filled in',    //新密码不能为空
    'new_confirm_pad' => 'New password and confirmation password are inconsistent, and modification failed',    //新密码和确认密码不一致，修改失败
    'old_pwd_error' => 'Original password is incorrect, verification failed',    //原始密码不正确，验证失败

    'create_account_pwd'              =>  'You  have  requested  to  create  an  account,  please  click  the  link  below  to  complete  activation  ',//您已请求创建帐户密码，请单击下面的链接以完成激活
    'within_24_hours'              =>  '(Valid  within  24  hours) ',//24小时内有效，否则无效

    //商品
    'goods_id_empty'                 => 'Product ID must be filled in',//'商品id不能为空',
    'goods_id_number'                => 'Product ID must be a number',//'商品id必须是数字',
    'goods_id_than_0'                 => 'Product ID must be an array',//'商品id必须是数组',
    'category_id_empty'                 =>'Category ID must be filled in',    //分类ID不能为空

    //订单
    'order_id_empty'          => 'Order ID must be filled in',   //订单ID不能为空
    'order_id_number'           => 'Order ID must be a number',   //订单ID必须是数字
    'order_id_than_0'               => 'Order ID must be greater than 0',   //订单ID必须大于0
    'order_status_empty'      => 'Payment status  must be filled in',    //支付状态不能为空
    'order_status_number'       => 'Payment status must be a digit',   //支付状态必须是数字
    'order_status_than_0'       => 'Payment status must be greater than 0',   //支付状态必须大于0
    'order_status_error'       => 'Incorrect payment status',   //支付状态不正确
    'shipping_mode_empty'           => 'Shipping method must be filled in',//运输方式不能为空
    'industry_term_empty'           => 'Trade terms must be filled in', //贸易术语不能为空
    'deposit_ratio_empty'           => 'Prepayments ratio must be filled in', //订金比例不能为空
    'deposit_ratio_error'            => 'Incorrect Prepayments ratio',     //订金比例不正确
    'address_abnormal'               => 'Abnormal delivery address',     //收货地址异常
    'order_goods_abnormal'           => 'The order item is abnormal',     //订单商品异常
    'submit_success'                => 'Order has been submitted successfully',     //提交订单成功
    'submit_failed'                  => 'Order submission failed',     //提交订单失败
    'state_type_empty'              => 'Status type must be filled in',//状态类型不能为空
    'order_type_empty'              => 'Order type must be filled in',//订单类型不能为空'
    'order_sn_empty'                      =>  'Order number  must be filled in',//订单编号不能为空
    'freezing_days_empty'                  =>  'Frozen days  must be filled in',     //冻结天数不能为空
    'freezing_reason_empty'                  =>  'Reasons for freezing  must be filled in',        //冻结原因不能为空
    'order_exception'                  =>  'Abnormal order',     //订单异常

    'order_status'                  =>  'Incorrect  Order  status',     //订单状态错误
    'audit_status'                  =>  'Company information not submitted or passed',     //资质未提交或审核不通过

    //订单状态
    'all_buyer'                                                     =>'All buyer to do',//全部待买家处理
    'all_seller'                                                    =>'All seller to do',//全部待卖家处理
    'all_order'                                                     =>'All orders',//全部订单
    'order_confirmed'                                               =>'To be confirmed',//待评估确认
    'pay_deposit'                                                   =>'Prepayments to be paid',//待付定金
    'deposit_examined'                                              =>'Pending prepayments',//待审核定金
    'production'                                                    =>'In production',//生产中
    'pay_payment'                                                   =>'Outstanding balance',//待付尾款
    'balance_payment'                                               =>'Pending balance',//待审核尾款
    'send_the_goods'                                                =>'To be delivered',//待发货
    'for_the_goods'                                                 =>'To be received',//待收货
    'been_completed'                                                =>'Completed',//已完成
    'been_cancelled'                                                =>'Cancelled',//已取消
    'distributed'                                                   =>'Order to be distributed',//待分发订单

    //售后状态
    'confirmed_by'                                                  =>'Confirmed_by',//确认通过
    'refused_to'                                                    =>'Refused to',//拒绝
    'a_refund'                                                      =>'A refund',//退款
    'the_delivery'                                                  =>'The delivery',//发货
    //售后
    'refund_id_empty' =>        'After-sale ID  must be filled in',     //售后ID不能为空
    'refund_type_empty'         => 'After-sales type ID  must be filled in',   //售后类型ID不能为空
    'refund_type_number'        => 'After-sales type must be a digit',   //售后类型必须是数字
    'description_empty'         => 'Problem description must be filled in',   //问题描述不能为空
    'file_url_empty'            => 'Uploaded file cannot be empty',   //上传文件不能为空
    'returned_goods_empty'      => 'Returned goods must be filled in',   //退货商品不能为空
    'returned_goods_array'      => 'Returned goods must be an array',   //退货商品必须是数组
    'after_sales_cond'              => 'The current order does not meet the after-sales conditions',     //当前订单不符合申请售后条件
    'returned_goods_quantity'              => 'The quantity of returned goods must be greater than 0',     //退换货的商品数量必须大于0
    'returned_goods_num'              => 'The number of returned goods cannot exceed the number of purchased goods',       //退换货的商品数量不得大于购买的商品数量
    'set_number'                        => 'Cannot  apply  for  after-sales  if  the  delivery  time  exceeds  the  set  number  of  days',//收货时间超过设定天数不能申请售后
    'after_sales_success'              => 'Successfully applied for after sales',     //申请售后成功
    'after_sales_failed'              => 'Application for after sales failed',   //申请售后失败
    'confirm_receipt'              => 'Confirm Receipt',  //确认收货

    //支付
    'payment_status_error'           =>  'Incorrect payment status', //支付状态不正确
    'payment_success'                  => 'Successfully initiated payment',//发起支付成功
    'payment_abnormal'                  => 'Payment initiated abnormally',//发起支付异常
    'payment_initiate'                  => 'Failed to initiate payment',//发起支付失败



    //用户地址
    'first_name'                               =>'First name cannot be empty',      //姓氏不能为空
    'last_name'                                =>'Last name cannot be empty',      //名字不能为空
    'country_id'                               =>'Country/Region must be filled in',      //国家/地区不能为空
    'province_id'                              =>'Province/State must be filled in',      //省/洲不能为空
    'city'                                      =>'City must be filled in',      //城市不能为空
    'detailed_address'                        =>'Detailed address must be filled in',      //详细地址不能为空
    'phone'                                    =>'Phone must be filled in',      //电话不能为空
    'address_count'                           =>'There are already 10 shipping addresses. Failed to add address',  //已有10个收货地址，添加地址失败
    'add_address_success'                    =>'Added address successfully',   //添加地址成功
    'add_address_error'                      =>'Failed to add address',   //添加地址失败
    'address_id'                              =>'Address ID must be filled in', //地址ID不能为空
    'address_id_than_0'                              =>'Address ID must be greater than 0', //地址ID必须大于0
    'edit_address_success'                   =>'Edited address successfully',   //编辑地址成功
    'edit_address_error'                     =>'Failed to edit address',   //编辑地址失败
    'set_address_success'                    =>'Successfully set the default address',   //设置默认地址成功
    'set_address_error'                      =>'Failed to set default address',   //设置默认地址失败
    'address_num'                          =>  'There are already 10 delivery addresses,  reaching the upper limit, and failed to add addresses', //已有10个收货地址，达到上限，添加地址失败

    //用户品牌授权
    'brand_name_empty'              => 'Brand file cannot be empty',     //品牌名称不能为空
    'brand_file_empty'              => 'Brand file cannot be empty',     //品牌文件不能为空
    'auth_file_empty'                => 'Authorization file cannot be empty',     //授权文件不能为空
    'auth_deadline_empty'            => 'Authorization period must be filled in',     //授权有效期不能为空
    'auth_id_empty'                => 'Authorization ID must be filled in',   //授权ID不能为空
    'auth_file_not'                => 'Authorization  file  does  not  exist',   //授权文件不存在
    'order_has_been'                => 'This brand authorization has bound an order, deletion failed',   //有订单已绑定当前品牌，删除失败
    'auth_deadline'                => 'Authorization expired',   //品牌授权已过期
    //用户资质
    'company_name_empty'           => 'Company name must be filled in',   //公司名称不能为空
    'operating_country_empty'           => 'Operating country must be filled in',   //经营国家不能为空
    'operating_area_empty'          => 'Business area must be filled in',     //经营地区不能为空
    'business_license_empty'            => 'Business license cannot be empty',    //营业执照不能为空

    'first_name_empty'          => 'Surname must be filled in',   //姓氏不能为空
    'last_name_empty'           => 'Name must be filled in',    //名字不能为空
    'phone_empty'                   => 'Phone must be filled in',    //电话不能为空
    'country_code_empty'         => 'Country code must be filled in',    //国家代号不能为空
    'area_code_empty'           => 'Area code must be filled in',    //区号不能为空
    'country_empty'             => 'Country must be filled in',    //国家不能为空
    'province_empty'            => 'Province/State must be filled in',    //省/州不能为空
    'city_empty'                => 'City must be filled in',    //城市不能为空
    'detailed_address_empty'    => 'Street address must be filled in',    //街道地址不能为空

    //运输方式
    'shipping'  =>  'By sea',//海运
    'Air freight'  =>  'By air',//空运
    'Courier'  =>  'By express',//快递

    'no_reviewed'   =>  'To be reviewed',//待审核
    'reviewed'      =>  'Reviewed',//已审核

    //聊天
    'toUid_empty'   => 'toUid must be filled in',
    'please_login'   => 'please login ',
    'customer_id_empty'   => 'customer_id must be filled in',
    'file_extension' =>'Only doc, docx, xls, xlsx, pdf files can be uploaded',
    'image_extension' =>'Only gif, jpg, jpeg, bmp, png files can be uploaded',
    'incorrect_type'                  =>  'Incorrect type',     //类型错误
];
//var_dump(\think\Config::get('zh-cn_api'));
