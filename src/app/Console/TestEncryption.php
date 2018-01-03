<?php

namespace Solunes\Pagostt\App\Console;

use Illuminate\Console\Command;

class TestEncryption extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-pagostt-encryption';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revisa el sistema de encriptado.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        $texto = 'TExto de muestra';
        $this->info('Comenzando la prueba. Texto: '.$texto);
        $encrypted = \Pagostt::encrypt($texto);
        $this->info('Texto Encriptado: '.$encrypted);
        $decrypted = \Pagostt::decrypt($encrypted);
        $this->info('Texto Decifrado: '.$decrypted);
    }
}
