<?php
namespace Plugins;

class FlashCard {
	public static function setFlashCard(string $title, string $uri, $message=""){
        if(session_status() != PHP_SESSION_ACTIVE){
	        session_name("_sA");
            session_start();
        }

        $content = array(
            "title" => $title,
            "uri" => $uri,
            "message" => $message
        );

        // Set default value of the flashCard
        if (!isset($_SESSION["_flashcard"])){
            $_SESSION["_flashcard"] = array();
        }

        array_push($_SESSION["_flashcard"], $content);
	}

	public static function getFlashCard() {
		if(session_status() != PHP_SESSION_ACTIVE){
			session_start();
		}

        $matchCards = array();

        // Make sure the _flashcard session is set
        if (empty($_SESSION["_flashcard"])){
            return array();
        }

        $flashCards = $_SESSION["_flashcard"];

		// Get the flashCards that matches the URI
		for ($i = 0; $i < count($flashCards); $i++) {
			$val = $flashCards[$i];
			if ($val["uri"] == \Route::$matchURI){
				array_push($matchCards, $val);

				// Remove From the session
				unset($_SESSION["_flashcard"][$i]);
			}
		}

        // Return the array of flashCards
        return $matchCards;
	}
}