# Gdoo协同办公

<p align="center">
<a href="http://www.gdoo.net"><img src="https://img.shields.io/badge/version-beta2.5.x-%23ff0000" alt="Build Status" /></a>
<a href="http://www.gdoo.net"><img src="https://img.shields.io/badge/laravel-8.0-%23ef3b2d" alt="laravel framework" /></a>
<a href="http://www.gdoo.net"><img src="https://img.shields.io/badge/MYSQL-8.0-%2300758f" alt="License" /></a>
<a href="http://www.gdoo.net"><img src="https://img.shields.io/badge/Licence-Apache2.0-blue.svg?style=flat" /></a>
</p>

## 介绍
1. 进销存功能、营销管理功能、简单生产计划、业务员销售团队分级管理、支持客户任务和业务员任务进度统计、支持客户自主下单。特别注重销售管理、业绩分析。
2. 主要为食品行业生产型和贸易企业定制开发。
3. 包括强大的自定义功能，模型字段(自定义跨表映射功能、视图管理、流程管理、字段权限管理)


## 架构
基于PHP框架Laravel 8.x + MySQL 8.x


## 申明
本项目包含部分企业版(未授权)的前端组件如aggrid、dhtmlxgantt。如果你使用请获得相关的授权。如果项目中有侵权问题请及时联系我，我会在第一时间调整。

## 模块
- [x] 基本功能
    - 新闻公告、讨论、项目任务、日程安排
- [x] 销售管理
    - 销售订单、样品申请、销售退货、生产计划等
- [x] 客户管理
    - 客户档案、客户联系人、开户申请、收货地址、客户开票档案、客户销售价格、销售团队等
- [x] 采购管理
    - 采购订单、采购入库单、原材料出库单、供应商档案
- [x] 费用管理
    - 促销申请、促销核销、进店申请、进店核销、客户费用、合同返利、合同补损、销售费用统计等
- [x] 库存管理
    - 发货、入库、其他出入、调拨、存货、仓库、统计报表等
- [x] 生产管理
    - 生产计划、用料计划、导出原料使用表等
- [x] 权限管理
    - 用户、角色、部门等
- [x] 应用管理
    - 模块、模型、应用管理等
- [x] 基本功能
    - 基础设置、微信公众号等
- [x] 报表(旧版)
    - 完成了基本报表，由于时间久远暂无自定义功能
- [x] 仪表盘自定义
    - 仪表盘支持简单的自定义、如快捷菜单、信息提示块、部件等

- [ ] 新角色权限(支持多角色,设定数据权限)
- [ ] 新销售组
- [ ] 新工作流程
- [ ] 新表单设计器
- [ ] 微信端h5(采用uniapp)
- [ ] 对话框页面管理

## 演示
演示地址: http://demo.gdoo.net 帐号：admin, 密码：123456

联系方式：15182223008(手机/微信)

QQ交流群: 79446405


## 截图
![首页](http://demo.gdoo.net/uploads/demo/1.png)

![日程](http://demo.gdoo.net/uploads/demo/2.png)

![项目](http://demo.gdoo.net/uploads/demo/3.png)

![销售订单](http://demo.gdoo.net/uploads/demo/4.png)

![销售订单](http://demo.gdoo.net/uploads/demo/11.png)

![表单视图](http://demo.gdoo.net/uploads/demo/5.png)

![列表视图](http://demo.gdoo.net/uploads/demo/6.png)

![流程设计](http://demo.gdoo.net/uploads/demo/7.png)

![模型字段](http://demo.gdoo.net/uploads/demo/8.png)

![单据管理](http://demo.gdoo.net/uploads/demo/9.png)

![表单权限](http://demo.gdoo.net/uploads/demo/10.png)

<p align="center">
<img src="http://demo.gdoo.net/uploads/demo/12.png" alt="h5兼容">
</p>

## 安装
1. 推荐使用宝塔面板，安装nginx 1.18.x、php-8.x(需要扩展：fileinfo)、mysql-8.x(mariaDB 10.4.x)，如果你使用win请自行安装相关环境
2. 创建网站和数据库, 数据库字符utf8mb4, 在网站目录中设置PHP命令行版本为php-80
3. 下载gdoo: https://gitee.com/hawind/gdoo 上传至宝塔网站根目录并解压
4. 打开Xshell并登录, 执行 <code>composer -v</code> 查看composer版本, 执行 <code>composer self-update</code> 升级composer至最新版本
5. 切换命令行到网站根目录 <code>cd /www/wwwroot/yousite</code>
6. 执行 <code>composer install --no-dev</code> 安装依赖
7. 执行 <code>cp .env.example .env</code> 并修改相关配置
8. 执行 <code>php artisan key:generate</code>
9. 打开宝塔数据库管理：
    1. 将项目database目录中的 gdoo-2.5.0.sql 文件上传并导入到数据库
10. 打开宝塔网站管理：
    1. 设置运行目录为public
    2. 设置伪静态为laravel5
11. 打开网站并用 <code>admin/123456</code> 登录

## 开发
1. 请在项目根目录执行 <code>yarn install</code> 安装前端依赖
2. 待续