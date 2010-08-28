<?

/***************************************************************************
 *
 * Copyright (c) by Andreas Unterkircher, unki@netshadow.at
 * All rights reserved
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 ***************************************************************************/

class MSCONFIG {

   /* Class constructor */
   function MSCONFIG($parent)
   {
      /* Nothing to do here */
   } // MSCONFIG()

   /* reads key=value pairs from config file */
   function readCfg($file)
   {
      /* Open the config file */
      if($config = @fopen($file, "r")) {

	 /* Read line by line */
	 while($line = fgets($config, 255)) {

	    /* cut away unneeeded things */
	    $line = trim($line);

	    if(!preg_match("/^#/", $line) && $line != "") {
					
	       /* extract data from string */
	       list($key, $value) = $this->getParams($line);
	       $value = str_replace("\"", "", $value);

               if($key != "" && $value != "") {
		  /* define configuartion parameter */
		  define($key, $value);
	       }

	    }

	 }

	 /* close config */
	 fclose($config);

	 return true;
      }
      else {
	 return false;
      }
   } // readCfg()

   /* split key=value pair */
   function getParams($line)
   {
      return split("=", $line);
   } // getParams()

}

?>
