### swoole学习
包含一个http server、一个tcp server，http server 
向后端tcp server发送tcp请求。tcp server处理完后返回给http
server，http server再返回给客户端浏览器

- 实现了自定义文本协议
- 实现长连接