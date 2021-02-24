# Gdoo协同办公

[![license][license-badge]][license-link]
[![release][release-badge]][release-link]

## 介绍
本系统是给企业定制开发的，基于laravel 8.x框架开发。基本进销存功能、营销管理功能、业务员销售团队分级管理、支持客户自主下单。


## 申明
本项目包含部分企业版(未授权)的前端组件如aggrid、dhtmlxgantt。如果你使用请获得相关的授权。开源此项目主要是学习和交流，由于是给企业定制的原因，大部分功能单一不完善，请不要直接投入使用。如果项目中有侵权问题请及时联系我，我会在第一时间调整。


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
- [ ] 新工作流程
- [ ] 新表单设计器
- [ ] 微信端h5(采用uniapp)
- [ ] 对话框页面管理

## 演示
演示地址: http://demo.gdoo.net 帐号：admin, 密码：123456

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

![h5兼容](http://demo.gdoo.net/uploads/demo/12.png)

## 架构
基于PHP框架Laravel 8.x + MySQL 8.x

## 安装
    1. 上传压缩包到目录，这里推荐使用宝塔面板，安装php-8.x、mysql-8.x、nginx
    2. 然后使用 composer install --no-dev 安装依赖
    3. 如果要修改前端文件请执行 yarn install 安装依赖
    4. 最后导入 database/gdoo-2.2.sql
    5. 然后执行 php artisan key:generate
    6. 修改.env相关配置

[license-badge]: https://img.shields.io/badge/license-apache2-blue.svg
[license-link]: LICENSE
[ci-badge]: https://github.com/hawind/gdoo/workflows/gdoo/badge.svg
[ci-link]: https://github.com/hawind/gdoo/actions?query=workflow:gdoo
[release-badge]: https://img.shields.io/github/release/hawind/gdoo.svg?style=flat-square
[release-link]: https://github.com/hawind/gdoo/releases