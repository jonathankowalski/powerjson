# powerjson
*json with great powers*

Use alias or variables in JSON


```
{
  "parent": "json://child.json?var=myvar"
}

{
  "child":"$var"
}
```

Results in :
```
{
  "parent": {
    "child": "myvar"
  }
}
```
