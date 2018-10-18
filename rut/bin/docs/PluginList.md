## FlashCard Class Static Public Function
FlashCard are used to create a temporary storage that can be use for a message notification and more.
#### setFlashCard function
Create a flashCard for a certain URI using ``setFlashCard()`` function. This function requires the following parameters:
* title ``default REQUIRED`` - this parameter will serve like a variable name of a flashcard
* uri ``deafault REQUIRED`` - this is the uri filter to avoid usage on non-target page.
* message ``default ''`` - this parameter will contain message string. This will serve like a variable value of a flash card.

#### getFlashCard function
To retrieve the saved flashCards use ``getFlashCard()`` function. This will return the array of flash cards saved within the matched URI.

## RecordTime Class Static Public Function
RecordTime Class is used to measure the execution time within the server. IN default this class is under the ``source code`` files of this framework. To turn off this feature go to ``config.json``.
#### start function
Start the recording using ``start()`` function.

#### end function
Stop the recording using ``end()`` function.

#### getTotal function
Get the total time consumed in millisecond using ``getTotal()`` function.