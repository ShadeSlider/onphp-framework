<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Builders
	**/
	final class SchemaBuilder extends BaseBuilder
	{
		public static function buildTable($tableName, array $propertyList)
		{
			$out = <<<EOT
\$schema->
	addTable(
		DBTable::create('{$tableName}')->

EOT;
			$propertyNames = array_keys($propertyList);
			$columns = array();
			$uniques = array();
			$indexes = array();

			/** @var MetaClassProperty $property */
			foreach ($propertyList as $property) {

				if (
					$property->getRelation()
					&& ($property->getRelationId() != MetaRelation::ONE_TO_ONE)
				) {
					continue;
				}

				
				/** Gathering indexes data */
				$indexName = $property->getIndex();
				if($indexName === "true") {
					$indexes[$property->getColumnName()] = array($property->getColumnName());
				}
				elseif(in_array($indexName, $propertyNames)) {
					throw new WrongArgumentException('Wrong index name ' . $indexName . '. Index name cannot be the same as any column name');
				}
				elseif(
					$property->getRelation()
					&& ($property->getRelationId() == MetaRelation::ONE_TO_ONE)
				) {
					try {
						$propertyType = $property->getType();
						if($propertyType instanceof ObjectType) {
							$propertyType->getClass(); //Check if class is internal
							$indexes[$property->getColumnName()] = array($property->getColumnName());
						}
					} catch(MissingElementException $e) {
						/* Internal class, no index is needed by default */
					}
				}
				elseif($indexName !== "false" && $indexName !== null) {
					if(!isset($indexes[$indexName])) {
						$indexes[$indexName] = array();
					}

					$indexes[$indexName][] = $property->getColumnName();
				}


				/** Gathering uniques data */
				$uniqueName = $property->getUnique();

				if($uniqueName === "true") {
					$uniques[$property->getColumnName()] = array($property->getColumnName());
				}
				elseif (in_array($uniqueName, $propertyNames)) {
					throw new WrongArgumentException('Wrong unique index name ' . $indexName . '. Unique index name cannot be the same as any column name');
				}
				elseif($uniqueName !== "false" && $uniqueName !== null) {
					if(!isset($uniques[$uniqueName])) {
						$uniques[$uniqueName] = array();
					}

					$uniques[$uniqueName][] = $property->getColumnName();
				}


				$column = $property->toColumn();
				
				if (is_array($column)) {
					$columns = array_merge($columns, $column);
				}
				else {
					$columns[] = $property->toColumn();
				}
			}
			
			$out .=
				implode("->\n", $columns);

			$lastUnique = end($uniques);
			foreach ($uniques as $uniqueName => $uniqueData) {
				$uniqueFieldsString = '"' . implode('", "', $uniqueData) . '"';

				$out .= "->\naddNamedUnique('{$uniqueName}', {$uniqueFieldsString})";
			}

			$lastIndex = end($indexes);
			foreach ($indexes as $indexName => $indexData) {
				if(isset($uniques[$indexName])) {
					continue;
				}

				$indexFieldsString = '"' . implode('", "', $indexData) . '"';

				$out .= "->\naddNamedIndex('{$indexName}', {$indexFieldsString})";
			}

			return $out."\n);\n\n";
		}
		
		public static function buildRelations(MetaClass $class)
		{
			$out = null;
			
			$knownJunctions = array();
			
			foreach ($class->getAllProperties() as $property) {
				if ($relation = $property->getRelation()) {
					
					$foreignClass = $property->getType()->getClass();

					if (
						$relation->getId() == MetaRelation::ONE_TO_MANY
						// nothing to build, it's in the same table
						// or table does not exist at all
						|| !$foreignClass->getPattern()->tableExists()
						// no need to process them
						//|| $class->getParent() //Why???
					) {
						continue;
					} elseif (
						$relation->getId() == MetaRelation::MANY_TO_MANY
					) {
						$junctionTableName =
							$class->getTableName()
							.'__'
							.$foreignClass->getTableName();

						if (isset($knownJunctions[$junctionTableName]))
							continue; // collision prevention
						else
							$knownJunctions[$junctionTableName] = true;
						
						$foreignPropery = clone $foreignClass->getIdentifier();
						
						$name = $class->getName();
						$name = strtolower($name[0]).substr($name, 1);
						$name .= 'Id';
						
						$foreignPropery->
							setName($name)->
							setColumnName($foreignPropery->getConvertedName())->
							// we don't need primary key here
							setIdentifier(false);
						
						// we don't want any garbage in such tables
						$property = clone $property;
						$property->required();
						
						// prevent name collisions
						if (
							$property->getRelationColumnName()
							== $foreignPropery->getColumnName()
						) {
							$foreignPropery->setColumnName(
								$class->getTableName().'_'
								.$property->getConvertedName().'_id'
							);
						}
						
						$out .= <<<EOT
\$schema->
	addTable(
		DBTable::create('{$junctionTableName}')->
		{$property->toColumn()}->
		{$foreignPropery->toColumn()}->
		addUniques('{$property->getRelationColumnName()}', '{$foreignPropery->getColumnName()}')
	);


EOT;

						$sourceColumn = $property->getRelationColumnName();
						$targetTable = $foreignClass->getTableName();
						$targetColumn = $foreignClass->getIdentifier()->getColumnName();

						$out .= <<<EOT
// {$junctionTableName}.{$sourceColumn} -> {$targetTable}.{$targetColumn}
\$schema->
	getTableByName('{$junctionTableName}')->
		getColumnByName('{$sourceColumn}')->
			setReference(
				\$schema->
					getTableByName('{$targetTable}')->
					getColumnByName('{$targetColumn}'),
				ForeignChangeAction::cascade(),
				ForeignChangeAction::cascade()
			);


EOT;

						$sourceColumn = $foreignPropery->getRelationColumnName();
						$targetTable = $class->getTableName();
						$targetColumn = $class->getIdentifier()->getColumnName();

						$out .= <<<EOT
// {$junctionTableName}.{$sourceColumn} -> {$targetTable}.{$targetColumn}
\$schema->
	getTableByName('{$junctionTableName}')->
		getColumnByName('{$sourceColumn}')->
			setReference(
				\$schema->
					getTableByName('{$targetTable}')->
					getColumnByName('{$targetColumn}'),
				ForeignChangeAction::cascade(),
				ForeignChangeAction::cascade()
			);


EOT;

					} else {
						$sourceTable = $class->getTableName();
						$sourceColumn = $property->getRelationColumnName();
						
						$targetTable = $foreignClass->getTableName();
						$targetColumn = $foreignClass->getIdentifier()->getColumnName();
						
						$out .= <<<EOT
// {$sourceTable}.{$sourceColumn} -> {$targetTable}.{$targetColumn}
\$schema->
	getTableByName('{$sourceTable}')->
		getColumnByName('{$sourceColumn}')->
			setReference(
				\$schema->
					getTableByName('{$targetTable}')->
					getColumnByName('{$targetColumn}'),
				ForeignChangeAction::restrict(),
				ForeignChangeAction::cascade()
			);


EOT;
					
					}
				}
			}
			
			return $out;
		}
		
		public static function getHead()
		{
			$out = parent::getHead();
			
			$out .= "\$schema = new DBSchema();\n\n";
			
			return $out;
		}
	}
?>