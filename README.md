#onPHP-framework extended

###This fork of onPHP has several convenient features added:
  
---

####New meta builder options for automatic table creation and SQL queries execution:
1. **--run-alter-table-queries** - automatically execute suggested SQL queries.
2. **--create-tables** - if --run-alter-table-queries is specified, create missing tables.
3. **--sql-log-file** - if supplied along with --create-tables and/or --run-alter-table-queries will write changes to the specified file.

---

####New meta attributes for managing regular and unique indexes in a database:
Meta now supports 2 new attributes for **<property>** tag: **index** and **unique**.
Both attributes can have 3 types of value:  

1\. **"true"** - create index / unique index for this column.

2\. **"false"** - never create index for this column. Also drops existing index if it's name matches following regex:  
```regex
/^(?:[\w_0-9]+?)(?:_u?idx__)([\w_0-9]+)$/i
```

3\. **"\<index_name\>"** - create index / unique index with a name **\<index_name\>**.  
If several properties have the same index / unique name a multi column index will be created.


---

###Usage:
Given meta
```xml
<!-- Employee -->
<class name="Employee">
    <properties>
        <identifier type="Integer" />
        <property   name="email"                type="String"   size="255"                                              unique="true"       />
        <property   name="login"                type="String"   size="255"                                              unique="true"       />
        <property   name="firstName"            type="String"   size="255"                          required="true"     index="full_name"   />
        <property   name="lastName"             type="String"   size="255"                          required="true"     index="full_name"   />
        <property   name="middleName"           type="String"   size="255"                                              index="full_name"   />
        <property   name="mobilePhone"          type="String"   size="255"                                              index="true"        />
        <property   name="workPhone"            type="String"   size="255"                                                                  />
        <property 	name="gender"               type="String"   size="1"       default="u"                                                  />
        <property   name="position"             type="String"   size="255"                                                                  />

        <property 	name="birthDate"            type="Date"                                                             index="true"        />
    </properties>
    <pattern name="StraightMapping"/>
</class>
<!-- @end Employee -->
```

running the following command with a PostgreSQL connection set up

```bash
php <PATH_TO_BUILDER>/build.php --run-alter-table-queries --create-tables --sql-log-file=db/sql/onphp_log_`date +%Y_%m_%d`.sql <PATH_TO_CONFIG>config.inc.php <PATH_TO_META>meta.xml
```

will result in the following SQL to be executed and written (appended) to a  
**\<PROJECT_ROOT\>db/sql/onphp_log_2014_07_22.sql**  
file (current date will be used, of course)
```sql
CREATE TABLE "employee" (
    
);


CREATE SEQUENCE "employee_id_seq";
ALTER TABLE "employee" ADD COLUMN "id" INTEGER NOT NULL default nextval('employee_id_seq');
ALTER TABLE "employee" ADD PRIMARY KEY("id");
ALTER TABLE "employee" ADD COLUMN "email" CHARACTER VARYING(255) NULL;
ALTER TABLE "employee" ADD COLUMN "login" CHARACTER VARYING(255) NULL;
ALTER TABLE "employee" ADD COLUMN "first_name" CHARACTER VARYING(255) NOT NULL;
ALTER TABLE "employee" ADD COLUMN "last_name" CHARACTER VARYING(255) NOT NULL;
ALTER TABLE "employee" ADD COLUMN "middle_name" CHARACTER VARYING(255) NULL;
ALTER TABLE "employee" ADD COLUMN "mobile_phone" CHARACTER VARYING(255) NULL;
ALTER TABLE "employee" ADD COLUMN "work_phone" CHARACTER VARYING(255) NULL;
ALTER TABLE "employee" ADD COLUMN "gender" CHARACTER VARYING(1) NULL DEFAULT 'u';
ALTER TABLE "employee" ADD COLUMN "position" CHARACTER VARYING(255) NULL;
ALTER TABLE "employee" ADD COLUMN "birth_date" DATE NULL;
ALTER SEQUENCE "employee_id_seq" OWNED BY "employee"."id";

CREATE INDEX "full_name_idx__employee" ON "employee"("first_name", "last_name", "middle_name");

CREATE INDEX "mobile_phone_idx__employee" ON "employee"("mobile_phone");

CREATE INDEX "birth_date_idx__employee" ON "employee"("birth_date");

CREATE UNIQUE INDEX "email_uidx__employee" ON "employee"("email");

CREATE UNIQUE INDEX "login_uidx__employee" ON "employee"("login");
```
