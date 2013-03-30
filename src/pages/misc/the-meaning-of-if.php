<h1>The meaning of &quot;IF&quot; &ndash; An Introduction to Formal Logic</h1>

<p>
When I was introduced to formal logic in my first year of university, the
hardest thing for me was coming to understand what the word &quot;if&quot;
means. Its use in every day language is similar to its logical definition, but
differes just enough to cause great confusion.
</p>

<p>
In formal logic, words like "if", "and", "or", "but", and "not" are called
<b>junctors</b>. They take either one or two <b>truth values</b> (either true or
false) and produce another truth value. The simplest one is "not", which
just inverts the truth value you give it:
</p>

<center>
<strong>Truth Table for NOT</strong>
<table border="1">
<tr><th>Input</th><th>Output</th></tr>
<tr><td>True</td><td>False</td></tr>
<tr><td>False</td><td>True</td></tr>
</table>
</center>

<p>
If you apply NOT to true, you get false. If you apply NOT to false, you get
true. Logicians use the symbol <b>&not;</b> to denote the junctor NOT. So we can
write: 
</p>

<ul>
    <li>&not;true = false</li>
    <li>true = &not;false</li>
    <li>&not;true = &not;(&not;false)</li>
</ul>

<p>
The same can be done for the other junctors. Logicians use the <b>&and;</b>
symbol for AND, and they use the <b>&or;</b> symbol for OR. The definition of
AND is what you would expect. It takes two truth values and produces
true if both inputs are true and false if any one of them is false:
</p>

<center>
<strong>Truth Table for AND</strong>
<table border="1">
<tr><th>Input #1</th><th>Input #2<th>Output</th></tr>
<tr><td>False</td><td>False</td><td>False</td></tr>
<tr><td>False</td><td>True</td><td>False</td></tr>
<tr><td>True</td><td>False</td><td>False</td></tr>
<tr><td>True</td><td>True</td><td>True</td></tr>
</table>
</center>

<p>
The formal meaning of OR is different from how we use it in everday english. If
I asked you, "Do you want cake <em>or</em> pie?", I'm really asking you to pick
one or the other, but not both. So it seems that OR should be true when exactly
one of its inputs is true, but that's actually a different junctor called
<b>xor</b>. The right logical definition of OR is true when either <em>or
both</em> of the inputs are true, and is false only when both inputs are false:
</p>

<center>
<strong>Truth Table for OR</strong>
<table border="1">
<tr><th>Input #1</th><th>Input #2<th>Output</th></tr>
<tr><td>False</td><td>False</td><td>False</td></tr>
<tr><td>False</td><td>True</td><td>True</td></tr>
<tr><td>True</td><td>False</td><td>True</td></tr>
<tr><td>True</td><td>True</td><td>True</td></tr>
</table>
</center>

<p>
Now we can begin to write simple sentences in <b>predicate logic</b>. Predicate
logic works by combining <b>predicates</b> (think of them as variables that can hold
truth values) with junctors. For example, if the predicate H means, "I am
hungry", and the predicate S means, "I am sad", then the sentence "I am hungry
and I am not sad" is written like this: 
</p>

<p style="text-align:center;">
H &and; (&not;S)
</p>

<p>
This sentence is true when both H and &not;S are true. In other words, it is
true when H is true (I am happy) and S is false (I am not sad).
</p>

<p>
As another example, here is how you can write the sentence "Joey likes cats or
Joey likes dogs but Joey doesn't like fish", where C means "Joey likes cats",
D means "Joey likes dogs", and F means "Joey likes fish":
</p>

<p style="text-align:center;">
(C &or; D) &and; (&not;F)
</p>

<p>
Note that "but" means exactly the same thing as "and" in formal logic!
</p>

