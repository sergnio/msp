
alter table users modify column league_id text;
alter table temp_confirm modify column actiondate datetime;
delete from comments where id > 0;
delete from users where id > 4;
delete from temp_confirm where id > 0;
delete from sent_email where id > 0;
delete from picks where pick_id > 0;
delete from nspx_leagueplayer where userid > 0;
delete from nsp_loginhistory where loginid > 0;
delete from messages where id > 0;
delete from league where league_id > 0;
delete from inbox where id > 0;
delete from homepage_text where id > 0 and id < 181; 


select s1.week as thisweek,
(select if(s2.gametime > now(), 'ip', 'sc') from schedules as s2 where s2.week = thisweek order by s2.gametime desc limit 1 ) as s2
from schedules as s1
where s1.week >= 1
  and s1.week <= 3
group by s1.week





alter table sent_email add column body text;
alter table sent_email add column senddate   datetime DEFAULT '2016-07-11 12:00:00';
alter table sent_email add column signature  char(64) not null default 'no signature applied';
alter table sent_email add column userid     int not null default 0;
alter table sent_email add column concerning char(64) not null default 'none';
alter table sent_email add column status char(25)






ALTER TABLE league
  DROP COLUMN active_week;

ALTER TABLE users
  DROP COLUMN win,
  DROP COLUMN lose,
  DROP COLUMN push,
  DROP COLUMN win_week,
  DROP COLUMN lose_week,
  DROP COLUMN push_week,
  DROP COLUMN total_points,
  DROP COLUMN total_points_week;
  
  
  

ALTER TABLE users
  DROP COLUMN  created,                         
  DROP COLUMN  salt,                            
  DROP COLUMN  passwordchangerequired,          
  DROP COLUMN  passwordchangerequiredcountdown, 
  DROP COLUMN  dateoflastpasswordchange,        
  DROP COLUMN  loginattemptcount,               
  DROP COLUMN  loginlockouttime,                
  DROP COLUMN  lastloginattempt,                
  DROP COLUMN  lastlogin,                       
  DROP COLUMN  specialstatus;


alter table users
   add created                               timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   add salt                                  char(64) NOT NULL default '54c2932a2036332b01c67d9d65f5ee86',  
   add passwordchangerequired                int not null default 0, 
   add passwordchangerequiredcountdown       int not null default 10,
   add dateoflastpasswordchange              timestamp not null default '2016-07-25 21:00:00',
   add loginattemptcount                     int not null default 10,
   add loginlockouttime                      int not null default 5,
   add lastloginattempt                      timestamp not null default '2016-07-25 21:00:00',
   add lastlogin                             timestamp not null default '2016-07-25 21:00:00', 
   add specialstatus                         char(15) not null default 'normal';




CREATE TABLE nspx_leagueplayer (
  userid int(10) unsigned NOT NULL,
  leagueid int(10) unsigned NOT NULL,
  playername varchar(16) NOT NULL,
  active tinyint(1) DEFAULT '1',
  paid tinyint(1) DEFAULT '1',
  joindate datetime NOT NULL DEFAULT now(),
  UNIQUE KEY leagueid (leagueid,playername),
  UNIQUE KEY playername (playername)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;






// just a hack for league player names
create table nspx_leagueplayer (
   userid int unsigned not null,
   leagueid int unsigned not null,
   playername varchar(16) not null,
   unique key (league, playername)
   );

Drop table 
CREATE TABLE `nspx_leagueplayer` (
  `userid` int(10) unsigned NOT NULL,
  `leagueid` int(10) unsigned NOT NULL,
  `playername` varchar(16) NOT NULL,
  UNIQUE KEY `leagueid` (`leagueid`,`playername`),
  UNIQUE KEY `playername` (`playername`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
alter table nspx_leagueplayer add column active bool default true;

CREATE TABLE nspx_leagueplayer (
  userid int(10) unsigned NOT NULL,
  leagueid int(10) unsigned NOT NULL,
  playername varchar(16) NOT NULL,
  active tinyint(1) DEFAULT '1',
  paid tinyint(1) default 1,
  joindate date default curdate(),
  UNIQUE KEY leagueid (leagueid,playername),
  UNIQUE KEY playername (playername)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


alter table nspx_leagueplayer add column paid tinyint not null default 1;
alter table nspx_leagueplayer add column joindate datetime default now() not null;


alter table schedules add column season int default null;

alter table league add column active bool default 1 not null;

// Installed on drillbrain and local  4/25/2016 hfs
create unique index ndxconfirmcode on temp_confirm (confirm_code); */
commit;
CREATE INDEX lname_index ON users (lname);
create unique index ndxfieldname on homepage_text (field_name);

#create unique index ndxfieldname on homepage_text (field_name);
#delete from homepage_text where field_name = '0';
#select * from homepage_text where field_name = '0';
commit;

#delete from comments where id > 0;
#delete from users where id > 4;
alter table users modify column league_id text;

create table nsp_user (
   userid int unsigned not null auto_increment primary key,
   skeyid int unsigned not null,  /* key into memberships */
   loginname varchar(30) not null unique,    /* must be unique to site - nothing to do with league names */
   fname varchar(25),
   lname varchar(25),
   email01 varchar(30),
   email02 varchar(30),
   password char(64),
   passwordchangerequired boolean default false,
   dateoflastpasswordchange timestamp default now(),
   created timestamp default now(),
   position varchar(8) default 'player',  /* player or admin... get away from user */
   loginattemptcount int default 5,
   loginlockouttime timestamp default now(),
   lastloginattemptid int unsigned,  /* based on loginname used. */
   active boolean default true,
   userstatus varchar(10) default 'normal',
   readwrite boolean default true
   );
   
create table nsp_leaguemembership (
   pkeyid int unsigned not null,
   leagueid int unsigned not null,
   playername varchar(16) not null, /* must be unique within the league.  it cannot be changed */
   position int unsigned not null,
   active int unsigned not null default 1,
   skeyid int unsigned not null auto_increment
   
   );
   
create table nsp_weeks (
   pkeyid int unsigned not null,  // who, league
   week int unsigned not null,
   skeyid
   );
   
create table nsp_picks (
   pkeyid int unsigned not null,
   game int unsigned,
   homeaway char(1)
   );
   
CREATE TABLE league (
   league_id int(11) NOT NULL AUTO_INCREMENT,
   league_name varchar(255) NOT NULL,
   commissioner int(11) NOT NULL,            /* REMOVE to nsp_leaguemembership */
   found_date datetime NOT NULL,             /* creation date */
   league_type int(11) NOT NULL,             /* error 0, Pick'em 1, Knockout 2 */
   league_points int(11) NOT NULL,           /* error 0, point spreads, not used = 1, set by site admin = 2, set by commissioner = 3 */
   league_picks int(11) NOT NULL,            /* error 0, number of team picks allowed each week */
   league_push int(11) NOT NULL,             /* error 0, tie scores 0pts = 1, .5pts = 2, 1pt = 3 */
   lockoutmode int unsigned not null,        /* error 0, lockout set by gametime = 1, commissioner = 2, preset time = 3 */
   lockouttime datetime,                     /* used if lockout set to preset time lockout mode */
   lockoutmanual boolean,                    /* used if lockout mode is commissioner */
   activeweekselectmode int default 1,       /* active week follows the site's active week = 1, by commissioner = 2
   firstactiveweek int default 1,            /* late start pick'em leagues have no before this week.  it must be indicated since players may miss */
   active_week int(11) NOT NULL DEFAULT '1', /* there are 17 active weeks.  This is the league's active week, not the site's */
   PRIMARY KEY (league_id),
   unique key (league_name)
   );
   
create table nsp_leaguesettings (
   league_id int,
   pkeyid int,          /* back to week */
   lockoutmonday time,
   lockouttuesday time,
   lockoutwednesday time,
   lockoutthursday time,
   lockoutfriday time,
   lockoutsaturday time,
   lockoutsunday time,
   
   

create table nsp_loginhistory (
   loginid  int unsigned not null auto_increment primary key,
   loginusername varchar(30),
   loginsuccess int(1) NOT NULL DEFAULT '1',
   logintime timestamp default now(),
   loginagent varchar(128),
   loginhost varchar(30),
   loginremoteaddress varchar(20),
   loginremoteuser varchar(30),
   loginreferer varchar(60),
   loginquery varchar(30),
   loginserverport varchar(30),
   spare01 varchar(20)
   );
CREATE TABLE `nsp_user` (
  `userid` MEDIUMINT unsigned NOT NULL AUTO_INCREMENT,
  `loginname` char(16) NOT NULL,
  `fname` char(25) DEFAULT NULL,
  `lname` char(25) DEFAULT NULL,
  `email01` char(30) DEFAULT NULL,
  `email02` char(30) DEFAULT NULL,
  `password` char(64) DEFAULT NULL,
  `passwordchangerequired` tinyint(1) DEFAULT '0',
  `passwordchangerequiredcountdown` MEDIUMINT DEFAULT '10',
  `dateoflastpasswordchange` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin` tinyint(1) DEFAULT '0',
  `loginattemptcount` MEDIUMINT DEFAULT '5',
  `loginlockouttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastlogin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) DEFAULT '1',
  `userstatus` char(10) DEFAULT 'normal',
  `readwrite` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `loginname` (`loginname`),
  UNIQUE KEY `email01` (`email01`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE nsp_admin (
  site                     char(30) primary key default 'mysuperpicks',
  season                   int not null default 2016,
  analytics                int not null default 1,
  developmentmode          int not null default 1,
  sessionmessagereferencemode int not null default 1,
  writelog                 int not null default 1,
  writelogfilespec         char(50) not null default './ROACH.TXT',
  emailnoreply             char(30) not null  DEFAULT 'noreply@mysuperpicks.com',
  emailsiteadmin           char(255) not null  default 'mattleisen@yahoo.com',
  emailtositecontact       char(255) not null  default 'mattleisen@yahoo.com',
  emailfromsitecontact     char(50) not null  default 'info@yahoo.com',
  emailsitelimit_longterm           int not null default 2,
  emailsitelimit_shortterm          int not null default 1,
  emailsitelimit_longtermcount      int not null default 0,
  emailsitelimit_shorttermcount     int not null default 0,
  emailsitelimit_longtermbasetime   datetime default '2016-07-11 12:00:00',
  emailsitelimit_shorttermbasetime  datetime default '9999-12-31 12:00:00',
  linkconfirm              char(120) not null default 'http://www.mysuperpicks.com/register.php',
  linkcontact              char(120) not null default 'http://www.mysuperpicks.com/contact.php',
  passwordadmin2           char(64) DEFAULT NULL,
  passwordadmin2hint       char(120) DEFAULT 'no hint defined',
  passwordadmin2question   char(120) DEFAULT 'no question defined',
  siteactive               int not null default 2,
  sitemaintenancemessage text,
  sitemaintenancemessagedefault char(255) default 'The site is not currently available.',
  siteloginmessage         char(255) default null,
  siteloginmessageshow     int not null default 1,
  timezonedefault          char(50) not null default 'America/Chicago',
  loginattemptsactive      int not null default 1,
  loginattemptsallowed     int not null DEFAULT 5,
  loginlockouttimeminutes  int not null default 5,
  devemailnoreply          char(50) not null  DEFAULT 'noreply@mysuperpicks.com',
  devemailsiteadmin        char(255) not null  default 'shedd2013@yahoo.com',
  devemailtositecontact    char(255) not null  default 'shedd2013@yahoo.com',
  devemailfromsitecontact  char(50) not null  default 'info@yahoo.com',
  deverrormailto           char(255) not null  default 'shedd2013@yahoo.com',
  deverrormaillimit        int not null default 10,
  deverrormailcount        int not null default 0
)

      insert into nsp_admin
         (site)
      values
         ('mysuperpicks');
         
      insert into nsp_admin
         (site)
      values
         ('nflbrain');
         
      insert into nsp_admin
         (site)
      values
         ('nflx');
         
         