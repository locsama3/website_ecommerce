<?php 
	trait QueryBuilder{

		public $tableName = '';
		public $where = '';
		public $operator = '';
		public $selectField = '*';
		public $limit = '';
		public $orderBy = '';
		public $innerJoin = '';
		public $groupBy = '';

		public function table($tableName)
		{
			$this->tableName = $tableName;
			return $this;
		}

		// Lấy tất cả
		public function get()
		{
			$sqlQuery = "SELECT $this->selectField 
						 FROM $this->tableName 
						 $this->innerJoin
						 $this->where
						 $this->groupBy 
						 $this->orderBy
						 $this->limit";
			$sqlQuery = trim($sqlQuery);
			$query = $this->query($sqlQuery);

			// reset field
			$this->resetQuery();

			if(!empty($query)){
				return $query->fetchAll(PDO::FETCH_ASSOC);
			}	

			return false;	
		}

		// Lấy 1 phần tử
		public function first()
		{
			$sqlQuery = "SELECT $this->selectField 
						 FROM $this->tableName 
						 $this->innerJoin
						 $this->where 
						 $this->groupBy 
						 $this->orderBy
						 $this->limit";
			$sqlQuery = trim($sqlQuery);
			$query = $this->query($sqlQuery);

			// reset field
			$this->resetQuery();

			if(!empty($query)){
				return $query->fetch(PDO::FETCH_ASSOC);
			}	
			
			return false;
		}

		// Chọn cột
		public function select($field='*')
		{
			$this->selectField = $field;
			return $this;
		}

		//inner join
		public function join($tableName, $relationship)
		{
			$this->innerJoin .= "INNER JOIN ". $tableName ." ON ". $relationship ." ";
			return $this;
		}

		// Các loại Điều kiện 
		// 1.
		public function where($field, $compare, $value)
		{
			if(empty($this->where)){
				$this->operator = 'WHERE';
			}else{
				$this->operator = 'AND';
			}
			$this->where .= "$this->operator $field $compare '$value' ";
			return $this;
		}

		// 2.
		public function orWhere($field, $compare, $value)
		{
			if(empty($this->where)){
				$this->operator = 'WHERE';
			}else{
				$this->operator = 'OR';
			}
			$this->where .= "$this->operator $field $compare '$value' ";
			return $this;
		}

		// 3.
		public function whereLike($field, $value)
		{
			if(empty($this->where)){
				$this->operator = 'WHERE';
			}else{
				$this->operator = 'AND';
			}
			$this->where .= "$this->operator $field LIKE '$value' ";
			return $this;
		}

		// 4.
		public function whereIs($field, $value)
		{
			if(empty($this->where)){
				$this->operator = 'WHERE';
			}else{
				$this->operator = 'AND';
			}
			$this->where .= "$this->operator $field IS $value ";
			return $this;
		}

		// Limit
		public function limit($number, $offset = 0)
		{
			$this->limit = "LIMIT $offset, $number";
			return $this;
		}

		/* 
			ORDER BY id ASC
			$this->db->orderBy('id', 'desc')
			$this->db->orderBy('id ASC, name DESC')
		*/
		public function orderBy($field, $type = 'ASC')
		{
			$fieldArr = array_filter(explode(',', $field));

			if(!empty($fieldArr) && count($fieldArr) >= 2){
				//SQL order by multi
				$this->orderBy = "ORDER BY ". implode(',', $fieldArr);
			}else{
				$this->orderBy = "ORDER BY ". $field ." ". $type;
			}
			
			return $this;
		}

		// Group by
		public function groupBy($field)
		{
			$fieldArr = array_filter(explode(',', $field));

			if(!empty($fieldArr) && count($fieldArr) >= 2){
				//SQL group by multi
				$this->groupBy = "GROUP BY ". implode(',', $fieldArr);
			}else{
				$this->groupBy = "GROUP BY ". $field;
			}
			
			return $this;
		}

		// insert
		public function insert($data)
		{
			$tableName = $this->tableName;
			$insertStatus = $this->insertData($tableName, $data);

			return $insertStatus;
		}

		// last id
		public function lastId()
		{
			return $this->lastInsertId();
		}

		//update 
		public function update($data)
		{
			$whereUpdate = str_replace('WHERE', '', $this->where);
			$whereUpdate = trim($whereUpdate);

			$tableName = $this->tableName;

			$updateStatus = $this->updateData($tableName, $data, $whereUpdate);

			return $updateStatus; 
		}

		//delete
		public function delete()
		{
			$whereDelete = str_replace('WHERE', '', $this->where);
			$whereDelete = trim($whereDelete);

			$tableName = $this->tableName;

			$deleteStatus = $this->deleteData($tableName, $whereDelete);

			return $deleteStatus; 
		}

		public function resetQuery()
		{
			// reset field
			$this->tableName = '';
			$this->where = '';
			$this->operator = '';
			$this->selectField = '*';
			$this->limit = '';
			$this->orderBy = '';
			$this->innerJoin = '';
		}

	}

 ?>