# php-cuif
Console User Intefrace Framework for PHP

repo maintenance now is under https://github.com/salojc2006/cuif

My idea is get a easy way to make interactive console programs, with visual objects such windows, textboxes, grids, but no messing with code to get it.

To see how easy you can write an visual and interactive application, please checkout my MysqlNav expample under https://github.com/salojc2006/mysqlnav repo.

You will see how is easy to create windows, add controls, and subscribe their possible events to control the ui flow.
Also, you don't need worry about how show and refresh screen, CUIF make it for you. Just think in concrete actions like open(), close(), hide(), show(), maximize(), minimize(), and more...

Using salodev libraries, it allows you to make interesting things like timeout executions, intervals, async querys to MySQL Database, execute a command, and write in its stdin or read its stdout whitout blocking de application proccess. All this is possible because are written and thought for make non-blocking operations, and works with a Worker loop that mantains your progam alive.

There are many things to do still, but in principe I think that the idea is clear. Think the code or libraries in high level. It allow you code more simply and no mess with low level task, that in several cases that I have seen, ends very hard to maintain it

So.. what can you do with CUIF?? An image is better than much words:
![mysqlcliente](https://cloud.githubusercontent.com/assets/5316253/20042195/73349eba-a454-11e6-9003-123c341d0c5f.png)

Want you improve more stuffs to mysqlnav? fork it!
Also.. I am open for any suggestions, ideas or contributions!

I hope you find it useful.
