<?php

/*
 * This file is part of Quark.
 *
 * Quark is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * CAS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CAS.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Quark\Form;

use Laminas\Form\Form;

class ConfigForm extends Form
{
    public function init()
    {
        $this->add([
            'type' => 'Text',
            'name' => 'naan',
            'options' => [
                'label' => 'NAAN',
                'info' => 'Name Assigning Authority Number', // @translate
            ],
            'attributes' => [
                'id' => 'naan',
                'required' => true,
            ],
        ]);
    }
}
