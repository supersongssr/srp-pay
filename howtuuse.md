
##what's new
````

````

## 项目描述
````
1.SSR多节点账号管理面板，兼容SS、SSRR，需配合SSR或SSRR版后端使用
2.支持V2Ray
3.内含简单的购物、卡券、邀请码、推广返利&提现、文章管理、工单（回复带邮件提醒）等模块
4.用户、节点标签化，不同用户可见不同节点
5.SS配置转SSR(R)配置，轻松一键导入导出SS账号
6.单机单节点日志分析功能
7.账号、节点24小时和本月的流量监控
8.流量异常、节点宕机邮件或ServerChan及时通知
9.账号临近到期、流量不够会自动发邮件提醒，自动禁用到期、流量异常的账号，自动清除日志等各种强大的定时任务
10.后台一键添加加密方式、混淆、协议、等级
11.屏蔽常见爬虫、屏蔽机器人
12.支持单端口多用户
13.支持节点订阅功能，可自由更换订阅地址、封禁账号订阅地址、禁止特定型号设备订阅
14.支持多国语言，自带英日韩繁语言包
15.订阅防投毒机制
16.自动释放端口机制，防止端口被大量长期占用
17.有赞云支付
18.可以阻止大陆或者海外访问
19.中转节点（开发中）
20.强大的营销管理：PushBear群发消息
21.telegram机器人（开发中）
22.防墙监测，节点被墙自动提醒、自动下线（TCP阻断）
````

## 安装
#### 环境要求
````
PHP 7.1.3+ （必须）
MYSQL 5.5+ （推荐5.6）
内存 1G+ (推荐2G)
磁盘空间 10G+
PHP必须开启zip、xml、curl、gd2、fileinfo、openssl、mbstring组件
安装完成后记得编辑.env中 APP_DEBUG 改为 false
````

#### 编辑php.ini
````
找到php.ini
vim /usr/local/php/etc/php.ini
搜索disable_function
删除proc_开头的所有函数 以及 force_env这个函数
````

#### 拉取代码
````
cd /www/wwwroot/
git clone https://github.com/supersongssr/srp-pay.git
````
#### 配置数据库
````
1.创建一个utf8mb4的数据库
2.编辑 .env 文件，修改 DB_ 开头的值
3.导入 sql/db.sql 到数据库
````
#### 安装面板
````
cd ssrpanel/
cp .env.example .env
（然后 vi .env 修改数据库的连接信息）
php composer.phar install
php artisan key:generate
chown -R 777 storage
cd ../
chown -R www:www srp-pay
chmod -R a+x srp-pay
````
#### 加入NGINX的URL重写规则
````
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
````

#### 出现500错误
````
理论上操作到上面那些步骤完了应该是可以正常访问网站了，如果网站出现500错误，请看WIKI，很有可能是fastcgi的错误
请看WIKI：https://github.com/ssrpanel/ssrpanel/wiki/%E5%87%BA%E7%8E%B0-open_basedir%E9%94%99%E8%AF%AF
修改完记得重启NGINX和PHP-FPM
````

#### 密码错误
````
如果正确安装完成后发现admin无法登陆，请到SSRPanel目录下执行如下命令：
php artisan upgradeUserPassword
admin的密码将被改为admin
````

#### 重启NGINX和PHP-FPM
````
service nginx restart
service php-fpm restart
````

## 定时任务
````
crontab加入如下命令（请自行修改php、ssrpanel路径）：
* * * * * php /www/wwwroot/srp-pay/artisan schedule:run >> /dev/null 2>&1
注意运行权限，必须跟ssrpanel项目权限一致，否则出现各种莫名其妙的错误
例如用lnmp的话默认权限用户组是 www:www，则添加定时任务是这样的：
crontab -e -u www
注意，需要设置一下www用户，和相应的目录才可以
mkdir /home/www ; chown -R www:www /home/www/

还要加个 
26 4 * * * /www/wwwroot/srp-pay/queue.sh
这个是每天晚上4点 自动启动 queue.sh 发信队列的


还要执行一次：
./queue.sh 
这是队列发信的问题 ，需要执行这个才能队列发信。嘎嘎
````

## 邮件配置
###### SMTP
````
编辑 .env 文件，修改 MAIL_ 开头的配置
````

###### 使用Mailgun发邮件
````
编辑 .env 文件
将 MAIL_DRIVER 值改为 mailgun
然后编辑 config/services.php
请自行配置如下内容
'mailgun' => [
    'domain' => 'mailgun发件域名',
    'secret' => 'mailgun上申请到的secret',
],
````

###### 发邮件失败处理
````
出现 Connection could not be established with host smtp.exmail.qq.com [Connection timed out #110] 这样的错误
因为smtp发邮件必须用到25,26,465,587这四个端口，故需要允许这四个端口通信
````

## 英文版
````
修改 .env 的 APP_LOCALE 值为 en
语言包位于 resources/lang 下，可自行更改
````

## 日志分析（仅支持单机单节点）
````
找到SSR服务端所在的ssserver.log文件
进入ssrpanel所在目录，建立一个软连接，并授权
cd /home/wwwroot/ssrpanel/storage/app
ln -S ssserver.log /root/shadowsocksr/ssserver.log
chown www:www ssserver.log
````

## IP库
```
本项目使用的是纯真IP库，如果需要更新IP库文件，请上纯真官网把qqwry.dat下载并覆盖至 storage/qqwrt.dat 文件
项目里还自带了IPIP的IP库，但是未使用，有开发能力的请自行测试。
```

## HTTPS
```
将 .env 文件里的 REDIRECT_HTTPS 值改为true，则全站强制走https
```

## SSR(R)部署
###### 手动部署

- 无上报IP版本：
````
wget https://github.com/ssrpanel/shadowsocksr/archive/V3.2.2.tar.gz
tar zxvf V3.2.2.tar.gz
cd shadowsocksr
sh ./setup_cymysql2.sh
配置 usermysql.json 里的数据库链接，NODE_ID就是节点ID，对应面板后台里添加的节点的自增ID，所以请先把面板搭好，搭好后进后台添加节点
````

- 会上报在线IP版本：
```
https://github.com/ssrpanel/shadowsocksr
```

## 单端口多用户
````
编辑节点的 user-config.json 文件：
vim user-config.json
将 "additional_ports" : {}, 改为以下内容：
"additional_ports" : {
    "80": {
        "passwd": "统一认证密码", // 例如 SSRP4ne1，推荐不要出现除大小写字母数字以外的任何字符
        "method": "统一认证加密方式", // 例如 aes-128-ctr
        "protocol": "统一认证协议", // 可选值：orgin、verify_deflate、auth_sha1_v4、auth_aes128_md5、auth_aes128_sha1、auth_chain_a
        "protocol_param": "#", // #号前面带上数字，则可以限制在线每个用户的最多在线设备数，仅限 auth_chain_a 协议下有效，例如： 3# 表示限制最多3个设备在线
        "obfs": "tls1.2_ticket_auth", // 可选值：plain、http_simple、http_post、random_head、tls1.2_ticket_auth
        "obfs_param": ""
    },
    "443": {
        "passwd": "统一认证密码",
        "method": "统一认证加密方式",
        "protocol": "统一认证协议",
        "protocol_param": "#",
        "obfs": "tls1.2_ticket_auth",
        "obfs_param": ""
    }
},
保存，然后重启SSR(R)服务。
客户端设置：
远程端口：80
密码：password
加密方式：aes-128-ctr
协议：auth_aes128_md5
混淆插件：tls1.2_ticket_auth
协议参数：1026:@123 (SSR端口:SSR密码)
或
远程端口：443
密码：password
加密方式：aes-128-ctr
协议：auth_aes128_sha1
混淆插件：tls1.2_ticket_auth
协议参数：1026:SSRP4ne1 (SSR端口:SSR密码)
经实测，节点后端使用auth_sha1_v4_compatible，可以兼容auth_chain_a
注意：如果想强制所有账号都走80、443这样自定义的端口的话，记得把 user-config.json 中的 additional_ports_only 设置为 true
警告：经实测单端口下如果用锐速没有效果，很可能是VPS供应商限制了这两个端口
提示：配置单端口最好先看下这个WIKI，防止踩坑：https://github.com/ssrpanel/ssrpanel/wiki/%E5%8D%95%E7%AB%AF%E5%8F%A3%E5%A4%9A%E7%94%A8%E6%88%B7%E7%9A%84%E5%9D%91
````
## 代码更新
````
进入到SSRPanel目录下
1.手动更新： git pull
2.强制更新： sh ./update.sh 
如果你更改了本地文件，手动更新会提示错误需要合并代码（自己搞定），强制更新会直接覆盖你本地所有更改过的文件
如果更新完代码各种错误，请先执行一遍 php composer.phar install
这个不用考虑 ：如果 php composer.phar update 或者强制 sh ./update.sh ，然后 php composer.phar dumpautoload
````

## 校时
````
如果架构是“面板机-数据库机-多节点机”，请务必保持各个服务器之间的时间一致，否则会产生：节点的在线数不准确、最后使用时间异常、单端口多用户功能失效等。
推荐统一使用CST时间并安装校时服务：
vim /etc/sysconfig/clock 把值改为 Asia/Shanghai
cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime
重启一下服务器，然后：
yum install ntp
ntpdate cn.pool.ntp.org
````
#### 每日自动校时（crontab）
```
0 0 * * * /usr/sbin/ntpdate cn.pool.ntp.org 
```

## 二开规范
````
如果有小伙伴要基于本程序进行二次开发，自行定制，请谨记一下规则
1.数据库表字段请务必使用蟒蛇法，严禁使用驼峰法
2.写完代码最好格式化，该空格一定要空格，该注释一定要注释，便于他人阅读代码
3.本项目中ajax返回格式都是 {"status":"fail 或者 success", "data":[数据], "message":"文本消息提示语"}
````

## 错误Class translator does not exist
````
执行一遍这个即可
php composer.phar install
````