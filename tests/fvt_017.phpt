--TEST--
pdo_informix: Insert and retrieve a very large blob file. (byte column)
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
			$create = 'CREATE TABLE animals (id INTEGER, my_blob byte)';
			$result = $this->db->exec( $create );

            $fp = fopen( "large_blob.dat" , "rb" );
			$stmt = $this->db->prepare('insert into animals (id,my_blob) values (:id,:my_blob)');
			print "inserting from file stream\n";
			$stmt->bindValue( ':id' , 0 );
			$stmt->bindParam( ':my_blob' , $fp , PDO::PARAM_LOB );
			$stmt->execute();
			print "succesful\n";

			print "runnign query\n";
			$stmt = $this->db->prepare( 'select id,my_blob from animals' );

			$stmt->bindColumn( 'id' , $id );
			$stmt->bindColumn( 'my_blob' , $blob , PDO::PARAM_LOB );
			$rs = $stmt->execute();
			while ($stmt->fetch(PDO::FETCH_BOUND)) {
				var_dump( $id );
				var_dump( $blob );
                $fp = fopen( "large_blob_out.dat" , "wb" );
                echo "datalength: " . stream_copy_to_stream( $blob , $fp ) . "\n";
                system( "diff large_blob.dat large_blob_out.dat" );
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
datalength: 10000
done
