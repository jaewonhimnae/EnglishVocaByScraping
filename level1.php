<?php
//This is for scraping.
include('simple_html_dom.php');
//This is for putting the data into the file.
include('file_cache.class.php');

$cache_path =  __DIR__.'/cache';
// first make the cache folder for putting the scraped data 
//chomod is neeeded for Linux 
if( !is_dir($cache_path) ) {
    mkdir($cache_path);
    chmod($cache_path, 0707);
}

$cache_file = 'english';

$instance_option = array('_cache_path'=>$cache_path, 'file_extension'=>'.php');

$instance = new FileCache($instance_option);
$datas = array();

if( $datas = ($instance->get($cache_file)) ){

    $text_arr = $datas;

} else {
    // Create DOM from URL or file
    $html = file_get_html('https://ko.wiktionary.org/wiki/%EB%B6%80%EB%A1%9D:%EC%98%81%EC%96%B4_%EB%B6%88%EA%B7%9C%EC%B9%99_%EB%8F%99%EC%82%AC');

    //$parses = $html->find('table.datatable', 0);

    $text_arr = array();
    $i = 0;

    foreach( $html->find('table.datatable') as $el ) {
        foreach($el->find('tr') as $tr){
            
            if ($tr->find('td[colspan=*]') ) continue;
            if ( !($tr->find('td', 1)) ) continue;

            $text_arr[] = array(
                'id'=>$i,
                'voca'=>trim($tr->find('td', 0)->plaintext),
                'simple'=>trim($tr->find('td', 1)->plaintext),
                'past'=>trim($tr->find('td', 2)->plaintext),
                'mp3'=>'',
            );
        }

        $i++;
    }

    $ttl = 60 * 60 * 24 * 15;

    $instance->save($cache_file, $text_arr, $ttl);
}

include("header.php");

/*
$url = 'level1.json'; // path to your JSON file
$data = file_get_contents($url); // put the contents of the file into a variable
$level1 = json_decode($data); // decode the JSON feed
*/

shuffle($text_arr);

print_r($text_arr);
exit();

array_splice($text_arr, 20);

$level1 = $text_arr;
?>
<div class="quizPart" style="">
    <div class="progress">
        <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <span>Level 1</span>
    <p class="progressNumber" style="text-align: right; margin-bottom: -20px; margin-top: -22px;"></p>
    <br />


    <p style="margin-bottom: -10px; color: grey;">infinite</p>
    <h1 id="mainVoca"></h1>
    <div class="row">
        <input style="font-weight:bold" type="text" id="answerInput" class="form-control col-10 " id="answerInput" aria-describedby="answerInput" placeholder="">
        <a id="answerBtn" href="#" class="btn btn-primary answerBtn " tabindex="-1" role="button" aria-disabled="true">Submit</a>
    </div>

    <h2 class="timer" style="display:none"></h2>
    <br>
    <div class="row">
        <a id="fadesIn1" href="#" tabindex="-1" role="button" class="btn btn-secondary btn-lg col-2 fadesIn">&nbsp;&nbsp;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;
        <a id="fadesIn2" href="#" tabindex="-1" role="button" class="btn btn-secondary btn-lg col-2 fadesIn">&nbsp;&nbsp;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;
        <a id="fadesIn3" href="#" tabindex="-1" role="button" class="btn btn-secondary btn-lg col-2 fadesIn">&nbsp;&nbsp;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;
        <a id="fadesIn4" href="#" tabindex="-1" role="button" class="btn btn-secondary btn-lg col-2 fadesIn">&nbsp;&nbsp;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;
        <a id="fadesIn5" href="#" tabindex="-1" role="button" class="btn btn-secondary btn-lg col-2 fadesIn">&nbsp;&nbsp;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;
    </div>
    <br>

    <div class="row">
        <div class="col-3"></div>
        <a style="display:none" id="checkIncorrectBtn" href="#" class="btn btn-info checkIncorrectBtn col-2" tabindex="-1" role="button" aria-disabled="true">
            Check Incorrect Answers
        </a>
        <div class="col-1"></div>
        <a style="display:none" id="goTolevelTwoBtn" href="./level2.php" class="btn btn-success goTolevelTwoBtn col-2" tabindex="-1" role="button" aria-disabled="true">
            Go to Level 2
        </a>
        <div class="col-3"></div>
    </div>


    <div id="wrongAnswer" style="display:none;border: 1px solid grey;border-radius: 8px;margin-top: 10px; padding:20px;">
        <del class="wrongAnswerTyped">not yet available!</del>
        <h3> Wrong! Correct answer:</h3>
        <li style="display:block">
            <div>
                <a id="playPronunciation" href="#" style="fontSize:30px">
                    <i class="fas fa-play"></i>
                    <span id="answerSound" data-value=""></span>
                </a>
            </div>
            <div>
                <a id="playPronunciationTwo" href="#" style="fontSize:30px">
                    <i class="fas fa-play"></i>
                    <span id="answerSoundTwo" data-value=""></span>
                </a>
            </div>
            <div>
                <a id="playPronunciationThree" href="#" style="fontSize:30px">
                    <i class="fas fa-play"></i>
                    <span id="answerSoundThree" data-value=""></span>
                </a>
            </div>
        </li>
        <audio id="audio" controls="controls" style="display:none">
            <source id="audioSource">
            </source>
            Your browser does not support the audio format.
        </audio>
    </div>
</div>

<div class="resultPart" style="display:none">
    <h1>Vocaburaries that need studying more</h1>
    <div class="incorrectVoca"></div>

    <a id="goBackToQuizPart" href="#" class="btn btn-secondary goBackToQuizPart " tabindex="-1" role="button" aria-disabled="true">
        Go Back
    </a>
</div>

<script>

String.prototype.explode = function (separator, limit)
{
    const array = this.split(separator);
    if (limit !== undefined && array.length >= limit)
    {
        array.push(array.splice(limit - 1).join(separator));
    }
    return array;
};

    //set all the variables here 
    const mainVoca = document.querySelector('#mainVoca'),
        answerInput = document.querySelector('#answerInput'),
        answerBtn = document.querySelector('#answerBtn'),
        progressNumber = document.querySelector('.progressNumber'),
        wrongAnswer = document.querySelector('#wrongAnswer'),
        playPronunciation = document.querySelector('#playPronunciation'),
        answerSound = document.querySelector('#answerSound'),
        playPronunciationTwo = document.querySelector('#playPronunciationTwo'),
        answerSoundTwo = document.querySelector('#answerSoundTwo'),
        playPronunciationThree = document.querySelector('#playPronunciationThree'),
        answerSoundThree = document.querySelector('#answerSoundThree'),
        progressBar = document.querySelector('#progressBar'),
        wrongAnswerTyped = document.querySelector('.wrongAnswerTyped'),
        timer = document.querySelector('.timer'),
        fadesIn1 = document.querySelector('#fadesIn1'),
        fadesIn2 = document.querySelector('#fadesIn2'),
        fadesIn3 = document.querySelector('#fadesIn3'),
        fadesIn4 = document.querySelector('#fadesIn4'),
        fadesIn5 = document.querySelector('#fadesIn5'),
        fadesIn = document.querySelectorAll('.fadesIn'),
        incorrectVoca = document.querySelector('.incorrectVoca'),
        quizPart = document.querySelector('.quizPart'),
        resultPart = document.querySelector('.resultPart'),
        checkIncorrectBtn = document.querySelector('.checkIncorrectBtn'),
        goTolevelTwoBtn = document.querySelector('.goTolevelTwoBtn'),
        goBackToQuizPart = document.querySelector('.goBackToQuizPart');

    let incorrectVocas = []
    var myArray = ['simple', 'past']
    var randomTense = myArray[Math.floor(Math.random() * myArray.length)];
    let i = 0;
    <?php 
    /*
    var level1 = '<?php echo json_encode($level1); ?>'
    var level1Array = JSON.parse(level1);
    */
    ?>
    var level1Array = <?php echo json_encode($text_arr); ?>;

    checkIncorrectBtn.addEventListener('click', goToResultPart)

    function goToResultPart() {
        resultPart.style.display = "block";
        quizPart.style.display = "none";
    }
    goBackToQuizPart.addEventListener('click', goToQuizPart)

    function goToQuizPart() {
        resultPart.style.display = "none";
        quizPart.style.display = "block";
    }


    countDown1();

    var counter = 10;

    function countDown1() {
        counter = 10;
        var switch_on = false
        var counterhaha = setInterval(function() {
            if (i === 20) return false;

            if (answerBtn.classList.contains("disabled")) {
                answerBtn.classList.remove("disabled")
            }

            if ($(".fadesIn").hasClass("disabled")) {
                $(".fadesIn").removeClass("disabled")
            }

            timer.textContent = counter
            counter--

            if (counter < 8) {
                fadesIn1.classList.add("disabled")
            }
            if (counter < 6) {
                fadesIn2.classList.add("disabled")
            }
            if (counter < 4) {
                fadesIn3.classList.add("disabled")
            }
            if (counter < 2) {
                fadesIn4.classList.add("disabled")
            }
            if (counter === 0) {
                fadesIn5.classList.add("disabled")
                answerBtn.classList.add("disabled")
                clearInterval(counterhaha);
                $("#answerInput").click(function() {
                    wrongAnswer.style.display = 'none';
                    if (switch_on === true) return false
                    countDown1();
                    switch_on = true
                });
            }
        }, 1000);
    }

    // when hit Enter in Input,it will be submiited 
    answerInput.addEventListener("keyup", function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            answerBtn.click();
        }
    })

    progressNumber.textContent = i + '/' + level1Array.length
    progressBar.style.width = i / level1Array.length * 100 + '%'
    progressBar.setAttribute("aria-valuenow", i / level1Array.length * 100)
    mainVoca.textContent = level1Array[i].voca

    if (randomTense === 'simple') {
        answerInput.setAttribute("placeholder", "Type SIMPLE PAST of the word above")
    } else {
        answerInput.setAttribute("placeholder", "Type PAST PARTICIPLE of the word above")
    }

    $(document).ready(function() {

        $("#answerBtn").click(function() {

            if (answerInput.value == null || answerInput.value == "") return alert('write the value first');

            let guess = answerInput.value;

            $tmp_answer =  (randomTense === 'simple') ? level1Array[i].simple : level1Array[i].past;

            $right_answers = $.map( $tmp_answer.explode('/', 5) , $.trim);


            //if (guess === (randomTense === 'simple' ? level1Array[i].simple : level1Array[i].past)) {
                if (guess && $right_answers.includes(guess)) {

                $.ajax({
                    url: "./ajaxRight.php",
                    type: "POST",
                    data: {
                        i: i
                    },
                    dataType: 'JSON',
                    success: function(data) {
                        if (data === level1Array.length) {
                            checkIncorrectBtn.style.display = "block";
                            goTolevelTwoBtn.style.display = "block";
                            answerBtn.classList.add('disabled');
                            $(".fadesIn").addClass("disabled");
                        }
                        counter = 10;
                        i = data;
                        wrongAnswer.style.display = 'none';
                        answerInput.value = "";
                        if (!level1Array[i]) {
                            i = i - 1
                        }
                        mainVoca.textContent = level1Array[i].voca;
                        i = data;
                        progressNumber.textContent = i + '/' + level1Array.length;
                        progressBar.style.width = i / level1Array.length * 100 + '%'
                        progressBar.setAttribute("aria-valuenow", i / level1Array.length * 100)
                        randomTense = myArray[Math.floor(Math.random() * myArray.length)];

                        if (randomTense === 'simple') {
                            answerInput.setAttribute("placeholder", "Type SIMPLE PAST of the word above")
                        } else {
                            answerInput.setAttribute("placeholder", "Type PAST PARTICIPLE of the word above")
                        }

                    }, // End of success
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        alert(textStatus);
                    }
                });
            } else {
                $.ajax({
                    url: "./ajaxWrong.php",
                    type: "POST",
                    data: {
                        i: i
                    },
                    dataType: 'JSON',
                    success: function(data) {
                        if (data === level1Array.length) {
                            checkIncorrectBtn.style.display = "block";
                            goTolevelTwoBtn.style.display = "block";
                            answerBtn.classList.add('disabled');
                            $(".fadesIn").addClass("disabled");
                        }
                        counter = 10;
                        i = data;
                        progressBar.style.width = i / level1Array.length * 100 + '%'
                        progressBar.setAttribute("aria-valuenow", i / level1Array.length * 100)
                        progressNumber.textContent = i + '/' + level1Array.length;
                        wrongAnswerTyped.textContent = answerInput.value;
                        if (!level1Array[i]) {
                            i = i - 1
                        }
                        incorrectVocas = incorrectVocas.concat(level1Array[i].voca)
                        incorrectVoca.innerHTML = incorrectVocas.join(" ");
                        answerInput.value = "";
                        mainVoca.textContent = level1Array[i].voca;
                        wrongAnswer.style.display = 'block';
                        i = data;
                        answerSound.textContent = level1Array[i - 1].voca;
                        answerSound.dataset.dataValue = level1Array[i - 1].mp3;

                        answerSoundTwo.textContent = level1Array[i - 1].simple;
                        answerSoundTwo.dataset.dataValue = level1Array[i - 1].mp3;

                        answerSoundThree.textContent = level1Array[i - 1].past;
                        answerSoundThree.dataset.dataValue = level1Array[i - 1].mp3;

                        randomTense = myArray[Math.floor(Math.random() * myArray.length)];

                        if (randomTense === 'simple') {
                            answerInput.setAttribute("placeholder", "Type SIMPLE PAST of the word above")
                        } else {
                            answerInput.setAttribute("placeholder", "Type PAST PARTICIPLE of the word above")
                        }
                    }, // End of success
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        alert(textStatus);
                    }
                });
            }
        });
        $("#playPronunciation").click(function(event) {
            event.preventDefault()
            var elm = event.target;
            var audio = document.getElementById('audio');
            var source = document.getElementById('audioSource');
            source.src = elm.getAttribute('data-data-value');
            audio.load(); //call this to just preload the audio without playing
            audio.play(); //call this to play the song right away
        });

        $("#playPronunciationTwo").click(function(event) {
            event.preventDefault()
            var elm = event.target;
            var audio = document.getElementById('audio');
            var source = document.getElementById('audioSource');
            source.src = elm.getAttribute('data-data-value');
            audio.load(); //call this to just preload the audio without playing
            audio.play(); //call this to play the song right away
        });

        $("#playPronunciationThree").click(function(event) {
            event.preventDefault()
            var elm = event.target;
            var audio = document.getElementById('audio');
            var source = document.getElementById('audioSource');
            source.src = elm.getAttribute('data-data-value');
            audio.load(); //call this to just preload the audio without playing
            audio.play(); //call this to play the song right away
        });
    });
</script>

<?php
include("footer.php")
?>

<!-- // $.getJSON("level1.json", function(result) {
        //     // $.each(result, function(index) {
        //     //     $("#hello").append(result[index].id + "<br />");
        //     //     $("#hello").append(result[index].voca + "<br />");
        //     //     $("#hello").append(result[index].simple + "<br />");
        //     //     $("#hello").append(result[index].past + "<br />");
        //     // });
        //     
        // }); -->