package com.colosa.qa.automatization;

import org.junit.runner.RunWith;
import org.junit.runners.Suite;
import org.junit.runners.Suite.SuiteClasses;
import org.junit.AfterClass;
import org.junit.BeforeClass;
import com.colosa.qa.automatization.common.Browser;

@RunWith(value = Suite.class)
@SuiteClasses(value = { com.colosa.qa.automatization.tests.subprocessesTest.SubprocessesTest.class})
public class subprocessesTest {
    @AfterClass public static void tearDownClass() {
        Browser.close();
    }

}