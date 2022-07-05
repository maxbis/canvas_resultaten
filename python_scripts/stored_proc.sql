DELIMITER $$
CREATE OR REPLACE PROCEDURE updateResultaat( IN in_student_nr INT, IN in_module_id INT, IN in_update_all INT )

BEGIN

delete from resultaat where (in_update_all or student_nummer=in_student_nr) and (in_update_all or module_id=in_module_id);

insert into resultaat (course_id, module_id, module, module_pos, student_nummer, klas, student_naam, ingeleverd, ingeleverd_eo, punten, punten_max, punten_eo, laatste_activiteit,laatste_beoordeling, aantal_opdrachten)
SELECT
    a.course_id course_id,
    g.id module_id,
    case when d.naam is null then g.name else d.naam end module,
    case when d.pos is null then 999 else d.pos end module_pos,
    SUBSTRING_INDEX(u.login_id,'@',1) student_nummer,
    u.klas klas,
    u.name student_naam,
    SUM(case when s.workflow_state<>'unsubmitted' then 1 else 0 end) ingeleverd,
    SUM(case when s.workflow_state<>'unsubmitted' and a.name like '%eind%' then 1 else 0 end) ingeleverd_eo,
    sum(s.entered_score) punten,
    sum(a.points_possible) punten_max,
    sum(case when a.name like '%eind%' then s.entered_score else 0 end) punten_eo,
    max(submitted_at),
    max(case when s.grader_id>0 then graded_at else '1970-01-01 00:00:00' end),
    sum(1) aantal_opdrachten
FROM assignment a
join submission s on s.assignment_id= a.id join user u on u.id=s.user_id
join assignment_group g on g.id = a.assignment_group_id
left outer join module_def d on d.id=g.id
where (in_update_all or SUBSTRING_INDEX(u.login_id,'@',1) = in_student_nr)
and (in_update_all or g.id = in_module_id)
group by 1, 2, 3, 4, 5, 6,7;

END$$

-- CALL updateResultaat(1,1,1)

-- working on
DELIMITER $$
CREATE OR REPLACE PROCEDURE insertLog( IN in_message VARCHAR(300) )
BEGIN
    INSERT into log (message) values (in_message);
END$$

-- CALL insertLog('Message')

DELIMITER $$
CREATE OR REPLACE PROCEDURE finisImport()
BEGIN
insert into log (message) values(
    (select concat(sum(1), '-', sum(ingeleverd)) from resultaat)
);
END$$

DELIMITER $$
CREATE OR REPLACE PROCEDURE finisImport()
BEGIN
insert into log (message) values(
    (select concat('Import finished. records imported:',sum(1), ', sum(ingeleverd):', sum(ingeleverd)) from resultaat)
);
END$$