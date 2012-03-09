create table `address` (
	`id` int unsigned auto_increment primary key,
	`location` varchar(255) not null,
	unique key `address_location` (`location`)
) engine=innodb;

create table `house` (
	`id` int unsigned not null primary key auto_increment,
	`name` varchar(255) not null
) engine=innodb;

create table `wizard` (
	`id` int unsigned not null primary key auto_increment,
	`firstName` varchar(255) not null,
	`lastName` varchar(255) not null,
	`role` enum('teacher', 'student') null,
	`addressId` int unsigned null,
	constraint `fk_address_wizard` foreign key (`addressId`) references `address`(`id`) on delete set null
) engine = innodb;

create table `student` (
	`wizardId` int unsigned not null primary key,
	`houseId` int unsigned not null,
	`isDAMembmer` bool not null default 0,
	constraint `fk_wizard_student` foreign key (`wizardId`) references `wizard`(`id`) on delete cascade,
	constraint `fk_house_students` foreign key (`houseId`) references `house`(`id`) on delete restrict
) engine = innodb;

create table `course` (
	`id` int unsigned not null primary key auto_increment,
	`wizardId` int unsigned not null,
	`subject` varchar(255) null,
	constraint `fk_teacher_courses` foreign key (`wizardId`) references `wizard`(`id`)
) engine = innodb;

create table `enrollment` (
	`courseId` int unsigned not null,
	`wizardId` int unsigned not null,
	primary key (`courseId`, `wizardId`),
	constraint `fk_course_students` foreign key (`courseId`) references `course`(`id`) on delete cascade,
	constraint `fk_student_courses` foreign key (`wizardId`) references `student`(`wizardId`) on delete cascade
) engine = innodb;