<?php 
namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'email', 'password', 'token', 'confirmado'];

    public $id;
    public $nombre;
    public $email;
    public $password;
    public $password2;
    public $password_actual;
    public $password_nuevo;
    public $token;
    public $confirmado;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->password_actual = $args['password_actual'] ?? '';
        $this->password_nuevo = $args['password_nuevo'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
    }
    
    //validar login de usuarios
    public function validarLogin() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es Obligatorio';
        } else {
            if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                self::$alertas['error'][] = 'Email no valido';
            }
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'El password no puede ir vacio';
        }

        return self::$alertas;
    }

    public function validarPerfil() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es Obligatorio';
        }
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es Obligatorio';
        } else {
            if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                self::$alertas['error'][] = 'Email no valido';
            }
        }

        return self::$alertas;
    }

    //Validar para cuentas nuevas
    public function validarCuentaNueva() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es Obligatorio';
        }
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es Obligatorio';
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'El password no puede ir vacio';
        } else {
            if( strlen($this->password) < 6) {
                self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
            } else {
                if($this->password !== $this->password2) {
                    self::$alertas['error'][] = 'El password no coincide';
                }
            }
        }
        return self::$alertas;
    }

    //valida pass
    public function validarPassword() {
        if(!$this->password) {
            self::$alertas['error'][] = 'El password no puede ir vacio';
        } else {
            if( strlen($this->password) < 6) {
                self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
            }
        }
        return self::$alertas;
    }

    public function nuevoPassword() {
        if(!$this->password_actual) {
            self::$alertas['error'][] = 'El password actual no puede ir vacio';
        } else {
            if(!password_verify($this->password_actual, $this->password)) {
                self::$alertas['error'][] = 'Password incorrecto';
            } 
        }

        if(!$this->password_nuevo) {
            self::$alertas['error'][] = 'El password nuevo no puede ir vacio';
        } else {
            if( strlen($this->password_nuevo) < 6) {
                self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
            }
        }


        return self::$alertas;
    }

    public function sincronizarPassword() {
        $this->password = $this->password_nuevo;
        unset($this->password2);
        unset($this->password_actual);
        unset($this->password_nuevo);
    }

    //Hashea pass
    public function hashPassword() {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    //Generar un token
    public function crearToken() {
        $this->token = uniqid();
    }

    //Validar email
    public function validarEmail() {
        if(!$this->email) {
            self::$alertas['error'][] = 'Debe ingresar un email';
        } else {
            if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                self::$alertas['error'][] = 'Email no valido';
            }
        }
        return self::$alertas;
    }

}