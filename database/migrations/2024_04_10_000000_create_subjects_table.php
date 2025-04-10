$table->string('name');
$table->text('description');
$table->integer('Nivel')->comment('Número del curso en la secuencia (1, 2, 3, etc.)');
$table->string('profesor_asignado');
$table->string('Archivo')->nullable();
$table->string('imagen')->nullable(); 