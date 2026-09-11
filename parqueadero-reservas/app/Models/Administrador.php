<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Administrador extends Model
{
    protected $table = 'administrador';
    protected $primaryKey = 'no_documento_administrador';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
 
    protected $fillable = [
        'no_documento_administrador',
        'id_usuario',
    ];
 
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
 
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'no_documento_administrador', 'no_documento_administrador');
    }
}
 