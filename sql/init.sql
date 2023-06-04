
# テーブル person の作成
drop table if exists users; 
create table users(userId varchar(8) binary primary key,
                    password varchar(12) not null,
                    userName varchar(50) not null);