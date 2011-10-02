<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
require_once 'Zend/Validate/Interface.php';

class Bral_Validate_Messagerie_Destinataires implements Zend_Validate_Interface
{
    protected $_messages = array();

    public function __construct($estObligatoire)
    {
        $this->estObligatoire = $estObligatoire;
    }

    public function isValid($valeur)
    {
        $this->_messages = array();
        $valid = true;

        if ((mb_strlen($valeur) < 1) && ($this->estObligatoire === true)) {
            $this->_messages[] = "Ce champ est obligatoire";
            $valid = false;
        }

        // si le champ est vide, mais qu'il n'est pas obligatoire, on sort tout de suite
        if ((mb_strlen($valeur) < 1) && ($this->estObligatoire === false)) {
            return true;
        }

        if (mb_strlen($valeur) > 120) {
            $this->_messages[] = "Trop de Bralduns destinataires (vous pouvez en mettre environ 35 maximum)";
            $valid = false;
        }

        if ($valid) {
            if (!preg_match_all('`^([[:digit:]]+(,|[[:space:]])*)+$`', $valeur, $matches)) {
                $this->_messages[] = "Ce champ contient des caractères invalides";
                $valid = false;
            }
        }

        if ($valid) {
            $braldunTable = new Braldun();
            foreach ($matches[0] as $id) {
                $r = $braldunTable->findById(trim($id));
                if ($r == null || count($r) == 0) {
                    $this->_messages[] = "Le Braldûn est inconnu";
                    $valid = false;
                }
            }
        }

        return $valid;
    }

    public function getMessages()
    {
        return $this->_messages;
    }

    public function getErrors()
    {
        return $this->_messages;
    }
}