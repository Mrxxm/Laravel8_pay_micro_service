
## About Pay Micro Service

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Explanatory Chart

* 支付系统

1.appid分配  
2.token认证  
3.下单接口  
4.订单查询  
5.支付回调  
...

![](https://img9.doubanio.com/view/photo/l/public/p2628569584.jpg)

## Document

[接口文档](https://www.showdoc.com.cn/1187035000139620?page_id=6007545984218735)

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

1.nginx配置

```
server {
        ssl on;

        listen 443;

        ssl_certificate pay.kenrou.cn.pem;
        ssl_certificate_key pay.kenrou.cn.key;
        ssl_session_timeout 5m;
        ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE:ECDH:AES:HIGH:!NULL:!aNULL:!MD5:!ADH:!RC4;
        ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
        ssl_prefer_server_ciphers on;

        server_name  pay.kenrou.cn ;
        set $root /var/www/Laravel8_pay_micro_service/public;
	root /var/www/Laravel8_pay_micro_service/public;

    	error_log /var/log/nginx/laravel_8_pay.error.log;
    	access_log /var/log/nginx/laravel_8_pay.access.log;

    	#location /static {
   	 #   	try_files $uri $uri/ =404;
    	#}

    location / {
        #autoindex on;
        #autoindex_exact_size on;
        #autoindex_localtime on;
        if ( !-e $request_filename) {
            rewrite ^/(.*)$ /index.php/$1 last;
            break;
        }
    }

    location ~ .+\.php($|/) {
        fastcgi_pass 127.0.0.1:9001;
        fastcgi_index index.php;
        fastcgi_split_path_info ^((?U).+.php)(/?.+)$;
	fastcgi_param HTTPS on;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $root$fastcgi_script_name;
        include fastcgi_params;
    }


    location ~ .*\.(jpg|jpeg|gif|png|ico|swf)$  {
        expires 3y;
        gzip off;
    }
}
```

2.数据库备份[数据库](http://blog.kenrou.cn/laravel8_pay_micro_service.sql)
