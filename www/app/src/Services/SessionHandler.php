<?php
namespace Alph\Services;

class SessionHandler
{
    public function __construct()
    {
        // Declare session_name as "alph_sess"
        session_name("alph_sess");

        // Set handler to overide session
        session_set_save_handler(
            [$this, "_open"],
            [$this, "_close"],
            [$this, "_read"],
            [$this, "_write"],
            [$this, "_destroy"],
            [$this, "_gc"]
        );

        //register_shutdown_function([$this, "_close"]);

        // Start the session
        session_start();
    }

    public function _open($savePath, $sessionName)
    {
        if (!is_dir(DIR_SESS)) {
            mkdir(DIR_SESS, 0777);
        }

        return true;
    }

    public function _close()
    {
        // Close the database connection
        return true;
    }

    public function _read(string $id)
    {
        return (string)@file_get_contents(DIR_SESS . 'sess_' . session_id());
    }

    public function _write(string $id, $data)
    {
        return \file_put_contents(DIR_SESS . 'sess_' . session_id(), $data) !== false;
    }

    public function _destroy($id)
    {
        return unlink(DIR_SESS . 'sess_' . session_id());
    }

    public function _gc($max)
    {
        foreach (glob(DIR_SESS . 'sess_*') as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }
}
