<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
try {
    $phar = new Phar('berry.phar');
    $phar->setStub(file_get_contents('stub.php'));

    foreach (glob('../../*.txt') as $file1){
        $file2 = basename($file1);

        echo "Add: $file2\n";
        $phar->addFile($file1, $file2);
    }

    foreach (array('src', 'lib', 'ext') as $dir){
        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator('../../'.$dir)
        );

        foreach ($iter as $file1 => $object){
            $file2 = substr($file1, 6);

            if (is_dir($file1))
                continue;

            echo "Add: $file2\n";
            $phar->addFile($file1, $file2);

        }
    }
} catch (Exception $e){
    echo $e->getMessage();
}
