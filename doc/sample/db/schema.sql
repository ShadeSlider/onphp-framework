-- $Id$

create sequence message_id;

create table message(
	id bigint default nextval('message_id') primary key,
	nickname varchar(50) not null,
	name varchar(255) not null, -- aka subject, aka title
	content text not null,
	posted timestamp not null
);
