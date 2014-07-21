onPHP-framework extended
===============

##This fork of onPHP has several convenient features added:
###- new meta builder options for automatic table creation and SQL queries execution:
1. **--run-alter-table-queries** - automatically execute suggested SQL queries.
2. **--create-tables** - if --run-alter-table-queries is specified, create missing tables.
3. **--sql-log-file** - if supplied along with --create-tables and/or --run-alter-table-queries will write changes to the specified file.


###- new meta attributes for managing regular and unique indexes in a database:
Meta now supports 2 new attributes for **<property>** tag: **index** and **unique**.
Both attributes can have 3 types of value:  
1. **true** - create index / unique index for this column.  
2. **false** - never create index for this column. Also drops existing index if it's name matches following regex: **/^(?:[\w_0-9]+?)(?:_u?idx__)([\w_0-9]+)$/i**  
3. **\<index_name\>** - create index / unique index with a name **<index_name>**. If several properties have the same index / unique name a multi column index will be created.


---

##Usage:
```bash
php \<PATH_TO_BUILDER\>/build.php --run-alter-table-queries --create-tables --sql-log-file=db/sql/onphp_log_`date +%Y_%m_%d`.sql \<PATH_TO_CONFIG\>config.inc.php \<PATH_TO_META\>meta.xml
```