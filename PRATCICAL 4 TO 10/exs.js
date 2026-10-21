fetch("scriptjs.json")
.then(function(scriptjs)
{
    return scriptjs.json();
})


.then(function(x)
{
    document.getElementById("name").innerText="Name: " + x.name;
    document.getElementById("stream").innerText="Stream: " + x.stream;
    document.getElementById("college").innerText="College: " + x.college;
    document.getElementById("semester").innerText="Semester: " + x.semester;
    document.getElementById("city").innerText="City: " + x.city;
    document.getElementById("list1").innerText= x.list1;
    document.getElementById("list2").innerText=x.list2;

});



