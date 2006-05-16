--TEST--
pdo_informix: Insert and retrieve a very large clob file. (text column)
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
	require_once('fvt.inc');
	class Test extends FVTTest
	{
		public function runTest()
		{
			$this->connect();

			try {
				/* Drop the test table, in case it exists */
				$drop = 'DROP TABLE animals';
				$result = $this->db->exec( $drop );
			} catch( Exception $e ){}

			/* Create the test table */
			$create = 'CREATE TABLE animals (id INTEGER, my_clob text)';
			$result = $this->db->exec( $create );

            $fp = fopen( "large_clob.dat" , "r" );
			$stmt = $this->db->prepare('insert into animals (id,my_clob) values (:id,:my_clob)');
			print "inserting from file stream\n";
			$stmt->bindValue( ':id' , 0 );
			$stmt->bindParam( ':my_clob' , $fp , PDO::PARAM_LOB );
			$stmt->execute();
			print "succesful\n";

			print "runnign query\n";
			$stmt = $this->db->prepare( 'select id,my_clob from animals' );

			$stmt->bindColumn( 'id' , $id );
			$stmt->bindColumn( 'my_clob' , $clob , PDO::PARAM_LOB );
			$rs = $stmt->execute();
			while ($stmt->fetch(PDO::FETCH_BOUND)) {
				var_dump( $id );
				var_dump( $clob );
                $fp = fopen( "large_clob_out.dat" , "w" );
                echo "datalength: " . stream_copy_to_stream( $clob , $fp ) . "\n";
                system( "diff large_clob.dat large_clob_out.dat" );
			}
			print "done\n";
		}
	}

	$testcase = new Test();
	$testcase->runTest();
?>
--EXPECTF--
inserting from file stream
succesful
runnign query
string(1) "0"
resource(%i) of type (stream)
datalength: 60044
done
