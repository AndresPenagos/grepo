<?php
namespace App\Models;
use Jenssegers\Blade\Blade;
use App\Models\EmailService;
/**
 * TODO: Eliminar metodos no necesarios
 */
class EmailNotifications
{
    protected $blade;
    protected $emailService;
    public function __construct()
    {
        $path = realpath(__DIR__ . "/../../resourses/views");
        $this->blade = new Blade($path, __DIR__ . "/../../resourses/compiled");
        $this->emailService = new EmailService();
    }
    /**
     * Toma un nombre, correo electrónico y contraseña, presenta una plantilla de bienvenida y envía un
     * correo electrónico a la dirección de correo electrónico proporcionada.
     * 
     * @param string name El nombre del usuario
     * @param string email La dirección de correo electrónico del usuario
     * @param string password La contraseña del usuario
     * 
     * @return El valor de retorno del método sendEmail().
     */
    public function welcomeByEmail(string $name, string $email, string $password)
    {
        $html = $this->renderWelcomeTemplate($name, $email, $password);
        $success = $this->sendEmail($email, 'Soporte', $html);
        return $success;
    }
    public function NotificationByEmail(string $name, string $email, string $notification)
    {
        $html = $this->renderNotificationTemplate($name, $email, $notification);
        $success = $this->sendEmail($email, 'Soporte', $html);
        return $success;
    }
    public function RecoverPasswordByEmail(string $name, string $email, string $password)
    {
        $html = $this->renderRecoverPasswordTemplate($name, $email, $password);
        $success = $this->sendEmail($email, 'Soporte', $html);
        return $success;
    }

    protected function renderWelcomeTemplate(string $name, string $email, string $password)
    {
        return $this->blade->render('emails.welcome', [
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);
    }
    protected function renderRecoverPasswordTemplate(string $name, string $email, string $password)
    {
        return $this->blade->render('emails.recover-password', [
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);
    }
    protected function renderNotificationTemplate(string $name, string $email, string $notification)
    {
        return $this->blade->render('emails.notification', [
            'name' => $name,
            'email' => $email,
            'notification' => $notification
        ]);
    }
    
    protected function sendEmail(string $to, string $subject, string $html, string $file = null)
    {
        if ($file == null) {
            $file ='';
        }
        return $this->emailService->sendEmail($to, $subject, $html,$file);
    }

};