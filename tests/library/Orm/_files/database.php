<?php

return array(
	'address' => array(
		array(
			'id'         => '1'
			, 'location' => 'Number 4, Privet Drive'
		)
		, array(
			'id'         => '2'
			, 'location' => 'The Burrow'
		)
		, array(
			'id'         => '3'
			, 'location' => 'Game Hut at Hogwarts'
		)
		, array(
			'id'         => '4'
			, 'location' => 'Malfoy Manor'
		)
	)
	, 'house' => array(
		array(
			'id'     => '1'
			, 'name' => 'Gryffindor'
		)
		, array(
			'id'     => '2'
			, 'name' => 'Hufflepuff'
		)
		, array(
			'id'     => '3'
			, 'name' => 'Ravenclaw'
		)
		, array(
			'id'     => '4'
			, 'name' => 'Slytherin'
		)
	)
	, 'wizard' => array(
		array(
			'id'          => '1'
			, 'firstName' => 'Harry'
			, 'lastName'  => 'Potter'
			, 'role'      => 'student'
			, 'addressId' => '1'
		)
		, array(
			'id'          => '2'
			, 'firstName' => 'Hermoine'
			, 'lastName'  => 'Granger'
			, 'role'      => 'student'
			, 'addressId' => null
		)
		, array(
			'id'          => '3'
			, 'firstName' => 'Ron'
			, 'lastName'  => 'Weasley'
			, 'role'      => 'student'
			, 'addressId' => '2'
		)
		, array(
			'id'          => '4'
			, 'firstName' => 'Rubeus'
			, 'lastName'  => 'Hagrid'
			, 'role'      => 'teacher'
			, 'addressId' => '3'
		)
		, array(
			'id'          => '5'
			, 'firstName' => 'Minerva'
			, 'lastName'  => 'McGonagall'
			, 'role'      => 'teacher'
			, 'addressId' => null
		)
		, array(
			'id'          => '6'
			, 'firstName' => 'Remus'
			, 'lastName'  => 'Lupin'
			, 'role'      => 'teacher'
			, 'addressId' => null
		)
		, array(
			'id'          => '7'
			, 'firstName' => 'Severus'
			, 'lastName'  => 'Snape'
			, 'role'      => 'teacher'
			, 'addressId' => null
		)
		, array(
			'id'          => '8'
			, 'firstName' => 'Charity'
			, 'lastName'  => 'Burbage'
			, 'role'      => 'teacher'
			, 'addressId' => null
		)
		, array(
			'id'          => '9'
			, 'firstName' => 'Albus'
			, 'lastName'  => 'Dumbledore'
			, 'role'      => 'teacher'
			, 'addressId' => null
		)
		, array(
			'id'          => '10'
			, 'firstName' => 'Filius'
			, 'lastName'  => 'Flitwick'
			, 'role'      => 'teacher'
			, 'addressId' => null
		)
		, array(
			'id'          => '11'
			, 'firstName' => 'Aurora'
			, 'lastName'  => 'Sinistra'
			, 'role'      => 'teacher'
			, 'addressId' => null
		)
		, array(
			'id'          => '12'
			, 'firstName' => 'Pomona'
			, 'lastName'  => 'Sprout'
			, 'role'      => 'teacher'
			, 'addressId' => null
		)
		, array(
			'id'          => '13'
			, 'firstName' => 'Sybill'
			, 'lastName'  => 'Trelawney'
			, 'role'      => 'teacher'
			, 'addressId' => null
		)
		, array(
			'id'          => '14'
			, 'firstName' => 'Luna'
			, 'lastName'  => 'Lovegood'
			, 'role'      => 'student'
			, 'addressId' => null
		)
		, array(
			'id'          => '15'
			, 'firstName' => 'Neville'
			, 'lastName'  => 'Longbottom'
			, 'role'      => 'student'
			, 'addressId' => null
		)
		, array(
			'id'          => '16'
			, 'firstName' => 'Draco'
			, 'lastName'  => 'Malfoy'
			, 'role'      => 'student'
			, 'addressId' => '4'
		)
		, array(
			'id'          => '17'
			, 'firstName' => 'Justin'
			, 'lastName'  => 'Finch-Fletchly'
			, 'role'      => 'student'
			, 'addressId' => null
		)
		, array(
			'id'          => '18'
			, 'firstName' => 'Cho'
			, 'lastName'  => 'Chang'
			, 'role'      => 'student'
			, 'addressId' => null
		)
		, array(
			'id'          => '19'
			, 'firstName' => 'Hannah'
			, 'lastName'  => 'Abbott'
			, 'role'      => 'student'
			, 'addressId' => null
		)
		, array(
			'id'          => '20'
			, 'firstName' => 'Vincent'
			, 'lastName'  => 'Crabbe'
			, 'role'      => 'student'
			, 'addressId' => null
		)
	)
	, 'student' => array(
		array(
			'wizardId'      => '1'
			, 'houseId'     => '1'
			, 'isDAMembmer' => '1'
		)
		, array(
			'wizardId'      => '2'
			, 'houseId'     => '1'
			, 'isDAMembmer' => '1'
		)
		, array(
			'wizardId'      => '3'
			, 'houseId'     => '1'
			, 'isDAMembmer' => '1'
		)
		, array(
			'wizardId'      => '14'
			, 'houseId'     => '3'
			, 'isDAMembmer' => '1'
		)
		, array(
			'wizardId'      => '15'
			, 'houseId'     => '1'
			, 'isDAMembmer' => '1'
		)
		, array(
			'wizardId'      => '16'
			, 'houseId'     => '4'
			, 'isDAMembmer' => '0'
		)
		, array(
			'wizardId'      => '17'
			, 'houseId'     => '2'
			, 'isDAMembmer' => '1'
		)
		, array(
			'wizardId'      => '18'
			, 'houseId'     => '3'
			, 'isDAMembmer' => '1'
		)
		, array(
			'wizardId'      => '19'
			, 'houseId'     => '2'
			, 'isDAMembmer' => '1'
		)
		, array(
			'wizardId'      => '20'
			, 'houseId'     => '4'
			, 'isDAMembmer' => '0'
		)
	)
	, 'course' => array(
		array(
			'id'         => '1'
			, 'wizardId' => '4'
			, 'subject'  => 'Care of Magical Teachers'
		)
		, array(
			'id'         => '2'
			, 'wizardId' => '5'
			, 'subject'  => 'Transfiguration'
		)
		, array(
			'id'         => '3'
			, 'wizardId' => '6'
			, 'subject'  => 'Defense Against the Dark Arts'
		)
		, array(
			'id'         => '4'
			, 'wizardId' => '7'
			, 'subject'  => 'Potions'
		)
		, array(
			'id'         => '5'
			, 'wizardId' => '8'
			, 'subject'  => 'Muggle Studies'
		)
		, array(
			'id'         => '7'
			, 'wizardId' => '10'
			, 'subject'  => 'Charms'
		)
		, array(
			'id'         => '8'
			, 'wizardId' => '11'
			, 'subject'  => 'Astronomy'
		)
		, array(
			'id'         => '9'
			, 'wizardId' => '12'
			, 'subject'  => 'Herbology'
		)
		, array(
			'id'         => '10'
			, 'wizardId' => '13'
			, 'subject'  => 'Divination'
		)
	)
	, 'enrollment' => array(
		array(
			'courseId'   => '1'
			, 'wizardId' => '1'
		)
		, array(
			'courseId'   => '1'
			, 'wizardId' => '2'
		)
		, array(
			'courseId'   => '1'
			, 'wizardId' => '3'
		)
		, array(
			'courseId'   => '1'
			, 'wizardId' => '16'
		)
		, array(
			'courseId'   => '1'
			, 'wizardId' => '20'
		)
		, array(
			'courseId'   => '2'
			, 'wizardId' => '1'
		)
		, array(
			'courseId'   => '2'
			, 'wizardId' => '2'
		)
		, array(
			'courseId'   => '2'
			, 'wizardId' => '3'
		)
		, array(
			'courseId'   => '2'
			, 'wizardId' => '14'
		)
		, array(
			'courseId'   => '2'
			, 'wizardId' => '15'
		)
		, array(
			'courseId'   => '2'
			, 'wizardId' => '16'
		)
		, array(
			'courseId'   => '2'
			, 'wizardId' => '17'
		)
		, array(
			'courseId'   => '2'
			, 'wizardId' => '18'
		)
		, array(
			'courseId'   => '2'
			, 'wizardId' => '19'
		)
		, array(
			'courseId'   => '2'
			, 'wizardId' => '20'
		)
		, array(
			'courseId'   => '3'
			, 'wizardId' => '1'
		)
		, array(
			'courseId'   => '3'
			, 'wizardId' => '2'
		)
		, array(
			'courseId'   => '3'
			, 'wizardId' => '3'
		)
		, array(
			'courseId'   => '3'
			, 'wizardId' => '14'
		)
		, array(
			'courseId'   => '3'
			, 'wizardId' => '15'
		)
		, array(
			'courseId'   => '3'
			, 'wizardId' => '16'
		)
		, array(
			'courseId'   => '3'
			, 'wizardId' => '17'
		)
		, array(
			'courseId'   => '3'
			, 'wizardId' => '18'
		)
		, array(
			'courseId'   => '3'
			, 'wizardId' => '19'
		)
		, array(
			'courseId'   => '3'
			, 'wizardId' => '20'
		)
		, array(
			'courseId'   => '4'
			, 'wizardId' => '1'
		)
		, array(
			'courseId'   => '4'
			, 'wizardId' => '2'
		)
		, array(
			'courseId'   => '4'
			, 'wizardId' => '3'
		)
		, array(
			'courseId'   => '4'
			, 'wizardId' => '14'
		)
		, array(
			'courseId'   => '4'
			, 'wizardId' => '15'
		)
		, array(
			'courseId'   => '4'
			, 'wizardId' => '16'
		)
		, array(
			'courseId'   => '4'
			, 'wizardId' => '17'
		)
		, array(
			'courseId'   => '4'
			, 'wizardId' => '18'
		)
		, array(
			'courseId'   => '4'
			, 'wizardId' => '19'
		)
		, array(
			'courseId'   => '4'
			, 'wizardId' => '20'
		)
		, array(
			'courseId'   => '5'
			, 'wizardId' => '2'
		)
		, array(
			'courseId'   => '7'
			, 'wizardId' => '1'
		)
		, array(
			'courseId'   => '7'
			, 'wizardId' => '2'
		)
		, array(
			'courseId'   => '7'
			, 'wizardId' => '3'
		)
		, array(
			'courseId'   => '7'
			, 'wizardId' => '14'
		)
		, array(
			'courseId'   => '7'
			, 'wizardId' => '15'
		)
		, array(
			'courseId'   => '7'
			, 'wizardId' => '16'
		)
		, array(
			'courseId'   => '7'
			, 'wizardId' => '17'
		)
		, array(
			'courseId'   => '7'
			, 'wizardId' => '18'
		)
		, array(
			'courseId'   => '7'
			, 'wizardId' => '19'
		)
		, array(
			'courseId'   => '7'
			, 'wizardId' => '20'
		)
		, array(
			'courseId'   => '8'
			, 'wizardId' => '1'
		)
		, array(
			'courseId'   => '8'
			, 'wizardId' => '2'
		)
		, array(
			'courseId'   => '8'
			, 'wizardId' => '3'
		)
		, array(
			'courseId'   => '9'
			, 'wizardId' => '1'
		)
		, array(
			'courseId'   => '9'
			, 'wizardId' => '2'
		)
		, array(
			'courseId'   => '9'
			, 'wizardId' => '3'
		)
		, array(
			'courseId'   => '9'
			, 'wizardId' => '14'
		)
		, array(
			'courseId'   => '9'
			, 'wizardId' => '15'
		)
		, array(
			'courseId'   => '9'
			, 'wizardId' => '16'
		)
		, array(
			'courseId'   => '9'
			, 'wizardId' => '17'
		)
		, array(
			'courseId'   => '9'
			, 'wizardId' => '18'
		)
		, array(
			'courseId'   => '9'
			, 'wizardId' => '19'
		)
		, array(
			'courseId'   => '9'
			, 'wizardId' => '20'
		)
		, array(
			'courseId'   => '10'
			, 'wizardId' => '1'
		)
		, array(
			'courseId'   => '10'
			, 'wizardId' => '3'
		)
	)
	, 'types' => array(
		array(
			'integer'   => 1
			, 'double'  => 1.3
			, 'string'  => 'string value'
			, 'date'    => Date::create('2000-01-01')
			, 'boolean' => false
			, 'enum'    => 1
		)
	)
);