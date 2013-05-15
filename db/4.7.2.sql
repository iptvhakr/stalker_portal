alter table `users` add `ls` int not null default 0;

set names utf8;
update `countries` set `name`='Кот-д\'Ивуар', `name_en`='Cote d\'Ivoire' where id=385;