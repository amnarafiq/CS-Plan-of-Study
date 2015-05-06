# CS-Plan-of-Study

This is the software to fill out the plan of study (PoS) for Computer Science graduate
students at Virginia Tech.  This was put together out of frustration using paper
forms and to reduce the amount of hand checking that had to be done with the
degree requirements.


## Structure of the software

This program is typically installed under the GPC directory and thus shares the php files stored in the directory above. Two particular files are of importance:

    require_once("../vendor/autoload.php");
    require_once("../dept_data.php");

The software requires several other pieces of software that are captured in the
composer.json file stored a directory above the PoS software.  Other than the 
requirement of `../vendor/` and `../dept_data.php` to be available in the directory 
containing the `pos/`, the software is self contained.

The `dept_data.php` contains constants and array definitions.
The `vendor` directory holds all the external tools installed with composer. This tool (POS) requires the template package (by yours truly) and a Markdown package.

## Templates

The software makes use the templates defined in 
`https://github.com/mapq/Template-System-in-PHP`. This simple template system
allows the use of variables, conditionals, and loops in the HTML for the system.
The template system merges an associative array (in PHP) with the templates to
produce the final HTML.

## Download of POS

The download of the PoS takes advantage of the fact that it is very easy to save and load a JSON file. The download of a PoS produces a text file with extension `.pos` that contains the json data for the PoS.  When the file is uploaded, it is simply read from file as text, parsed as JSON and converted to an assocative array in PHP, and this array is directly passed to the template (see previous section) for display.

## Files /pos

* courses.php, courses.tmpl -  show the listing of courses
* index.php, index.tmpl - show the landing page with the instructions
* library.php - common routines used in both the MS and PHD PoS

## Files /pos/ms

* index.php - code that handles the display of the HTML form for the MS PoS, handles the submission of the form, the printing of the form, and the download. When a form is submitted, a validation is done. If there are errors, they are passed back to the form and displayed among the form sections relevant to the errors. If the form is dowloaded, the error messages are included in the downloaded file.  If the form is printed, the messages are included in the printed form.

* instructions.php, instructions.tmpl - display instruction page

* ms.tmpl - main template file for the MS PoS, includes both MS Thesis and MS Coursework

* template files used in different sections of the ms.tmpl - 4000.tmpl, 5000.tmpl, 6000.tmpl, committee.tmpl, coursework.tmpl, courseworkprt.tmpl, examples.txt, print.css, print.tmpl, thesis.tmpl, thesisprt.tmpl, transfer.tmpl, transferexplanation.tmpl

## Files /pos/phd

* index.php - see notes above for the MS, same code basically but different checks... screams to be factored out and merged with MS code

* instructions.php, instructions.tmpl - display instruction page

* phd.tmpl - main template file for the PHD PoS

* template files used in different sections of the phd.tmpl -4000.tmpl, 5000.tmpl, 6000.tmpl, cognate.tmpl, committee.tmpl, examples.txt, print.css, print.tmpl, transfer.tmpl, transferexplanation.tmpl

## Disclaimer

Please don't use this code as an example of good programming; it was put together with bubble gum and duct tape.

`:-)`
