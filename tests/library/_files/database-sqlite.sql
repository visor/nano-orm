create table address(
	id integer primary key autoincrement,
	location varchar(255) not null
);

create table house(
	id integer primary key autoincrement,
	name varchar(255) not null
);

create table wizard(
	id integer primary key autoincrement,
	firstName varchar(255) not null,
	lastName varchar(255) not null,
	role varchar(20) default null,
	addressId integer default null,
	constraint fk_address_wizard foreign key (addressId) references address(id) on delete set null
);

create table student(
	wizardId integer primary key,
	houseId integer not null,
	isDAMembmer bool not null default 0,
	constraint fk_wizard_student foreign key (wizardId) references wizard(id) on delete cascade,
	constraint fk_house_students foreign key (houseId) references house(id) on delete restrict
);

create table course(
	id integer primary key autoincrement,
	wizardId integer not null,
	subject varchar(255) default null,
	constraint fk_teacher_courses foreign key (wizardId) references wizard(id)
);

create table enrollment(
	courseId integer not null,
	wizardId integer not null,
	primary key (courseId, wizardId),
	constraint fk_course_students foreign key (courseId) references course(id) on delete cascade,
	constraint fk_student_courses foreign key (wizardId) references student(wizardId) on delete cascade
);

create unique index address_location on address(location);