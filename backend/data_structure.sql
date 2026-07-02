-- goralys database schema
-- version 3.0.1

set foreign_key_checks = 0;

-- -----------------------------------------------------
-- public ids table
-- -----------------------------------------------------
drop table if exists public_ids;
create table public_ids
(
    username  varchar(32) not null unique, -- fk -> users_info.username
    public_id uuid        not null unique,

    foreign key (username) references users_info (username)
        on delete cascade
        on update cascade
) engine = innodb;

-- -----------------------------------------------------
-- users table (main active accounts)
-- This table is purely used for auth purposes
-- -----------------------------------------------------
drop table if exists users;
create table users
(
    id            int auto_increment primary key,
    username      varchar(32)                          not null unique, -- e.g. "j.dupont3", fk -> users_info.username
    password_hash varchar(255),
    role          enum ('teacher', 'student', 'admin') not null,
    created_at    datetime default current_timestamp,

    foreign key (username) references users_info (username)
        on delete cascade
        on update cascade
) engine = innodb;

-- -----------------------------------------------------
-- users_info table (names source of truth)
-- This table is used to get accurate and "official" (no user input) data for the subjects export
-- -----------------------------------------------------
drop table if exists users_info;
create table users_info
(
    username  varchar(32) not null unique, -- e.g. "j.dupont3"
    firstname varchar(30) not null,
    lastname  varchar(70) not null,

    primary key (username)
) engine = innodb;

-- -----------------------------------------------------
-- emails table
-- -----------------------------------------------------
drop table if exists emails;
create table emails
(
    username varchar(32)  not null unique, -- fk -> users.username
    email    varchar(255) not null unique,

    foreign key (username) references users (username)
        on delete cascade
        on update cascade
) engine = innodb;

-- -----------------------------------------------------
-- tickets table
-- -----------------------------------------------------
drop table if exists tickets;
create table tickets
(
    id         bigint auto_increment                                                     not null unique primary key,
    reason     enum ('password-forgot', 'subject-error', 'personal-info-error', 'other') not null,
    opener     varchar(32), -- fk -> users.username
    email      varchar(255),
    message    varchar(255),
    created_at timestamp default current_timestamp,

    foreign key (opener) references users (username)
        on delete cascade
        on update cascade
) engine = innodb;

-- -----------------------------------------------------
-- admins_list table (only source to create admins)
-- -----------------------------------------------------
drop table if exists admins_list;
create table admins_list
(
    username varchar(32) not null unique, -- fk -> users_info.username

    primary key (username),
    foreign key (username) references users_info (username)
) engine = innodb;

-- -----------------------------------------------------
-- topics table
-- -----------------------------------------------------
drop table if exists topics;
create table topics
(
    id         int auto_increment primary key,
    topic_code varchar(32)  not null unique, -- e.g. "maths_2025_jd"
    name       varchar(100) not null
) engine = innodb;

-- -----------------------------------------------------
-- student_topics table (many-to-many)
-- -----------------------------------------------------
drop table if exists student_info, student_topics;
create table student_topics
(
    student_username     varchar(32) not null, -- fk -> users_info.username
    topic_id             int         not null, -- fk -> topics.id
    subject              varchar(255),
    last_rejected        varchar(255),
    teacher_comment      varchar(255),
    draft_path           varchar(255),
    subject_status       tinyint(1) default 0, -- 0=not submitted, 1=submitted, 2=rejected, 3=approved
    is_interdisciplinary bool       default false,
    last_updated_at      timestamp  default current_timestamp on update current_timestamp,
    primary key (student_username, topic_id),
    foreign key (topic_id) references topics (id)
        on delete cascade
        on update cascade,
    foreign key (student_username) references users_info (username)
        on delete cascade
        on update cascade
) engine = innodb;

-- -----------------------------------------------------
-- student_info table
-- This table is used to get accurate and "official" (no user input) data for the subjects export
-- -----------------------------------------------------
create table students_classroom
(
    username varchar(32) not null unique, -- fk -> student_topics.student_username
    class    varchar(5)  not null,

    primary key (username),
    foreign key (username) references student_topics (student_username)
        on update cascade
        on delete cascade
) engine = innodb;

-- -----------------------------------------------------
-- topic_teachers table
-- -----------------------------------------------------
drop table if exists topic_teachers;
create table topic_teachers
(
    topic_id         int,                  -- fk -> topics.id
    teacher_username varchar(32) not null, -- fk -> users_info.username
    primary key (topic_id, teacher_username),
    foreign key (topic_id) references topics (id)
        on delete cascade
        on update cascade,
    foreign key (teacher_username) references users_info (username)
) engine = innodb;


set foreign_key_checks = 1;

-- -----------------------------------------------------
-- public ids triggers
-- -----------------------------------------------------
drop trigger if exists trg_after_insert_admin;
create trigger trg_after_insert_admin
    after insert
    on admins_list
    for each row
    insert into public_ids (username, public_id)
    values (new.username, uuid());

drop trigger if exists trg_after_insert_teacher;
create trigger trg_after_insert_teacher
    after insert
    on topic_teachers
    for each row
    insert ignore into public_ids (username, public_id)
    values (new.teacher_username, uuid());

drop trigger if exists trg_after_insert_student;
create trigger trg_after_insert_student
    after insert
    on student_topics
    for each row
    insert ignore into public_ids (username, public_id)
    values (new.student_username, uuid());