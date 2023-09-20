# phadmin
一款基于phalcon的快速后台开发管理框架，后台带有MVC生成器，让您将更多的精力放在系统业务逻辑开发上，含完整的管理员权限管理，后台菜单管理，基础的用户管理及附件管理等。

# 软件架构
PHP框架基于性能相对于其他框架均略胜一筹的phalcon框架（4.0.5版本），前端基于受欢迎且易用的Layui框架，编辑器默认使用kindeditor，只需设计好数据表，一键生成控制器，模型，视图，及操作菜单，方便快捷。

# 安装教程
1.  操作系统建议使用Linux系统，在win下可使用虚拟机
2.  phadmin系统默认需要使用redis缓存及Seaslog日志组件
3.  phalcon（PHP框架，在PHP扩展中添加）、redis可在宝塔面板中进行安装，Seaslog安装方法，登录服务器终端运行安装命令：pecl install seaslog
4.  在宝塔面板中创建站点（PHP版本建议7.3，Mysql建议5.7以上），将项目文件上传至站点目录，
5.  编辑站点，网站目录需指向public目录，伪静态设置参考下方的”伪静态“
6.  创建数据库（建议字符集及排序使用utf8mb4和utf8mb4_general_ci），将项目根目录下的phadmin.sql导入数据库。
7.  修改/app/config/database.php文件，修改为正确的数据库名，账号密码等，
8.  访问后台：http://你的域名/backend/login
9.  后台默认账号及密码：admin、admin888
10. 建议：/app/common/models、/app/modules/backend、/public/static/modules，这些目录设置为可写入和更改，可直接给777权限，因为生成器需要向这几个目录写入控制器，模型，视图，JS脚本文件

# 环境建议
Linux + Nginx + PHP7.3 + MySQL8

# 伪静态
```html
    location / {
        try_files $uri /index.php?_url=$uri&$args;
    }
```

# 使用说明
1.  默认要安装Seaslog日志扩展
2.  设计数据表时应设计有id自增主键
3.  注意：生成有关联数据时，应先生成待关联的子表，再生成主数据表，如文章及文章分类，应先生成文章分类表（生成文章分类时若关联自身，则会认为是无限级分类），再生成文章表
4.  api接口模块默认使用了简单的随机字符及时间戮的签名验证

# 图例
![一](https://github.com/fade-name/phadmin/blob/main/public/assets/1.png)
![二](https://github.com/fade-name/phadmin/blob/main/public/assets/2.png)
![三](https://github.com/fade-name/phadmin/blob/main/public/assets/3.png)
![四](https://github.com/fade-name/phadmin/blob/main/public/assets/4.png)
![五](https://github.com/fade-name/phadmin/blob/main/public/assets/5.png)

# 联系方式
有问题或意见建议请联系QQ：2295943610，或邮件至：2295943610@qq.com

# 捐赠
若此项目能得到你的青睐，欢迎捐赠支持作者持续开发与维护，感谢所有支持开源的朋友

![六](https://github.com/fade-name/phadmin/blob/main/public/assets/6.jpg)
![七](https://github.com/fade-name/phadmin/blob/main/public/assets/7.jpg)
![测试](https://github.com/fade-name/phadmin/blob/c31b7e553c910a9ed3d15b351f0c986f7f14d6fb/public/assets/1.png)
