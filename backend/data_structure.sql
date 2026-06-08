-- goralys database schema
-- version 2.3

-- makes sure all previous tables are deleted
drop table if exists student_topics, topic_teachers, topics, admins_list, users, public_ids, emails;

-- -----------------------------------------------------
-- public ids table
-- -----------------------------------------------------

create table public_ids
(
    username  varchar(32) not null unique,
    public_id uuid        not null unique
) engine = innodb;

-- -----------------------------------------------------
-- users table (main active accounts)
-- -----------------------------------------------------

create table users
(
    id            int auto_increment primary key,
    username      varchar(32)                          not null unique, -- e.g. "j.dupont3"
    full_name     varchar(100)                         not null,
    password_hash varchar(255),
    role          enum ('teacher', 'student', 'admin') not null,
    created_at    datetime default current_timestamp
) engine = innodb;

-- -----------------------------------------------------
-- emails table
-- -----------------------------------------------------

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

create table tickets
(
    id         bigint auto_increment                                                     not null unique primary key,
    reason     enum ('password-forgot', 'subject-error', 'personal-info-error', 'other') not null, -- fk -> users.username
    opener     varchar(32),
    email      varchar(32),
    message    varchar(255),
    created_at timestamp default current_timestamp,

    foreign key (opener) references users (username)
        on delete cascade
        on update cascade
) engine = innodb;

-- -----------------------------------------------------
-- admins_list table (only source to create admins)
-- -----------------------------------------------------
create table admins_list
(
    username varchar(32) not null unique
) engine = innodb;

-- -----------------------------------------------------
-- topics table
-- -----------------------------------------------------
drop table if exists topics;
create table topics
(
    id         int auto_increment primary key,
    topic_code varchar(32)  not null, -- e.g. "maths_2025_jd"
    name       varchar(100) not null
) engine = innodb;

-- -----------------------------------------------------
-- student_topics table (many-to-many)
-- -----------------------------------------------------
create table student_topics
(
    student_username     varchar(32) not null,
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
        on update cascade
) engine = innodb;

-- -----------------------------------------------------
-- student_info table
-- -----------------------------------------------------
create table student_info
(
    username  varchar(32) not null unique,
    firstname varchar(32) not null,
    lastname  varchar(32) not null,
    class     varchar(5)  not null,

    primary key (username, class),
    foreign key (username) references student_topics (student_username)
        on update cascade
        on delete cascade
) engine = innodb;

-- -----------------------------------------------------
-- topic_teachers table
-- -----------------------------------------------------
create table topic_teachers
(
    topic_id         int, -- fk -> topics.id
    teacher_username varchar(32) not null,
    primary key (topic_id, teacher_username),
    foreign key (topic_id) references topics (id)
        on delete cascade
        on update cascade
) engine = innodb;