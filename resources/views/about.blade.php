@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h1 class="text-center mb-4">Nuestra Historia</h1>
            
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Los Inicios</h2>
                    <p>Nuestra congregación nace, de parte de Dios, en el corazón de un ministro llamado José Bravo, quien había sido misionero en la República de Perú. De origen español, naturalizado belga, colaboraba con la Iglesia Christian Center, de las Asambleas de Dios estadounidense, radicada en este país, cuando Dios le llevó a dar los primeros pasos, con el apoyo y la colaboración de la hermana Antonia Laurel, de esta naciente obra hispana.</p>
                    
                    <p>Su primer esfuerzo lo inició apoyado por los alumnos del Seminario Teológico del Christian Center. El programa de conquista a la comunidad latina radicada en este país, empezó en una pequeña cafetería a quien le dieron el nombre de "Café le Messager" (Café el Mensajero) situado en la rue des Grands Carmes 19, 1000 Bruselas, ubicada junto a la Gran Plaza, en el centro de la ciudad.</p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Primeras Reuniones</h2>
                    <p>Inicialmente se tenían reuniones los días martes de 7 a 9 pm. El nombre de este ministerio fue "Comunión Iberoamericana en Bruselas". Nuestra primera reunión se celebró el primer domingo de abril de 1990, donde asistieron solamente los alumnos de la facultad de teología de la universidad protestante de Bruselas (Seminario Bíblico). Ante esta circunstancia, el Misionero José Bravo, en un acto de fe, predicó en español, aunque nadie le comprendió. Para la siguiente reunión empezaron a llegar los primeros frutos latinos.</p>
                    
                    <p>La comunidad inicial de latinos, que conformó la Iglesia, fue del Perú. Con el paso de los años también empezó a reunirse la comunidad ecuatoriana, colombiana, argentina, entre otras de América latina y Europa.</p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Desarrollo y Crecimiento</h2>
                    <p>En su primera etapa, luego de salir del Café donde se realizaban las reuniones, el Misionero José Bravo tomó contacto con el Pastor Amel, de la comunidad francófona, quien nos concedió el privilegio de utilizar su local, pues su congregación habían adquirido uno nuevo en otra comuna de Bruselas. Desde este momento, contando con un local, el grupo se pudo desarrollar legalmente bajo la cobertura del ASBL: "Eglise Evangélique de Saint-Josse-ten-Noode", ubicada en rue de Chalet 09, a partir del mes de septiembre de 1990. Sin embargo, seguíamos siendo una extensión del Christian Center.</p>
                    
                    <p>En el mes de junio de 1995, habiendo cumplido con algunos requisitos de la iglesia madre, se nos concedió el derecho de funcionar en forma independiente. El primer nombre de la iglesia fue Centro Evangelístico Iberoamericano, bajo la dirección del Misionero José Bravo.</p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Liderazgo y Cambios</h2>
                    <p>Luego, el primer Pastor latino que tomó la dirección de la iglesia fue la hermana Inés Facho, de nacionalidad peruana. Bajo su dirección fue cambiado el nombre de la iglesia por Centro Evangelístico Hispano. Hasta este momento seguíamos legalmente funcionado como "Eglise Evangélique de Saint-Josse-ten-Noode".</p>
                    
                    <p>Como encargada de la iglesia, la hermana Inés L. Facho Cruz, el 10 de abril de 1995, solicita al concilio de las Asambleas de Dios en Panamá, representada por el Rev. Lowell David, el envío del Pastor Misionero Edgar Alexis Vega Caballero y de su colaborador, Pastor Misionero, Gustavo Guerrero, como ministros de apoyo.</p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Consolidación</h2>
                    <p>Transcurrido un tiempo, nuestra hermana Inés Facho contrae matrimonio y viaja al Perú, donde ha continuado con su ministerio. Al retornar nuestra hermana a su país, la dirección pastoral quedó en manos del Misionero Gustavo Guerrero, ministro de las Asambleas de Dios de Panamá, hasta el año de 1998. A partir de este año, la responsabilidad quedó en manos del Pastor Edgar Vega, ministro de las Asambleas de Dios de Panamá, hasta hoy.</p>
                    
                    <p>En el mes de junio de 1999, habiendo logrado un crecimiento y capacidad administrativa a través de los miembros de nuestra congregación, nos constituimos en una iglesia bajo nuestro propio ASBL hasta la actualidad.</p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Reconocimiento Actual</h2>
                    <p>En asamblea general del 06 de diciembre del 2006, a causa de la proyección hacia la comunidad belga, se decidió cambiar en el nombre de la iglesia, el término "Hispano" por "Internacional", haciéndose la modificación oficial el 17 de septiembre del 2007. Hoy nuestra iglesia se denomina Centro Evangelístico Internacional.</p>
                    
                    <p>Nuestra congregación ha logrado el reconocimiento legal del gobierno belga, al ser parte del Sínodo Federal de Iglesias Protestantes y Evangélicas de Bélgica. Dentro del Sínodo general pertenecemos a las Iglesias Independientes.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: none;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .card-body {
        padding: 25px;
    }
    h1 {
        color: #2c3e50;
        font-weight: 700;
    }
    h2 {
        color: #3498db;
        font-weight: 600;
    }
    p {
        color: #34495e;
        line-height: 1.8;
        text-align: justify;
    }
</style>
@endsection