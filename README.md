#wormhole
gateway配置文件在config/gateway.php中，需要注意gateway协议和发送给后台的协议；

gateway加载内容在app/Gateways/中，start_* 负责gateway启动部分；

开发使用docker-compose,默认地址 nginx:80  / wormhole.dev


#monitor
monitor配置信息在 config/monitor.php中

配置只需要注意：monitor的host；

##开发
使用docker-compose，默认会使用 nginx:8000 / monitor.dev:8000

#配置
配置分两部分

.env 配置和 config/

##env
.env为优先配置，详见 .env文件

api配置，需要注意domain，如果domain不对，将拒绝访问

##开发
mysql host 直接使用mysql即可； 用户名／密码 参见 docker-compose

#wormhole服务操作

##启动所有服务
php artisan gatewayAll start --d

##停止所有服务
php artisan gatewayAll stop

##register启动
php artisan register start --d
 
##register 停止
php artisan register stop

##gateway启动
php artisan gateway start --d
 
##gateway 停止
php artisan gateway stop

##worker启动
php artisan worker start --d
 
##worker 停止
php artisan worker stop


#docker-compose

目前使用 https://github.com/laradock/laradock build开发环境
默认启动内容需要：
docker-compose up -d nginx mysql redis phpmyadmin

##docker-compose.yml配置
application 项目路径，参考 laradock 的readme

workspace build 之前修改 TZ=Asia/Shanghai

nginx 端口监听增加  8000:8000

nginx site配置，在laradock／nginx／site 文件夹下，配置monitor.dev:8000 / wormhole.dev:80




#wormhole 携带的 command


详见 app/Console/Commands/下

