<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class MetodoPago extends Model
{
    protected $table = 'metodo_pago';
    protected $primaryKey = 'id_metodo_pago';
    public $timestamps = false;
 
    protected $fillable = [
        'nombre_metodo_pago',
        'estado_metodo_pago',
    ];
 
    public function comprobantesPago()
    {
        return $this->hasMany(ComprobantePago::class, 'id_metodo_pago', 'id_metodo_pago');
    }
}
 