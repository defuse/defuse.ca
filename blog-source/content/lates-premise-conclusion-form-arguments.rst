[LaTeX] Typesetting Premise and Conclusion Form Arguments
##########################################################
:slug: latex-premise-conclusion-form-arguments
:author: Taylor Hornby
:date: 2013-02-23 00:00
:category: programming
:tags: latex

This is the easiest way I have found to typeset premise and conclusion style
arguments in LaTeX. First, add this to your preamble:

.. code:: latex

    \usepackage{amssymb}

Then use something like this to typeset the argument:

.. code:: latex

    \begin{itemize}
        \item[1.] Premise One.
        \item[2.] Premise Two.
        \item[3.] Premise Three.
        \item[$\therefore$] Therefore, Your Conclusion.
    \end{itemize}
