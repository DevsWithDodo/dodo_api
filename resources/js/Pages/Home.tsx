import React, { useEffect, useRef } from "react";
import "@/css/pages/home.scss";
import dodo from "@/img/dodo.png";
import play from "@/img/google_play_logo.png";
import appStore from "@/img/apple_logo.png";
import microsoft from "@/img/microsoft_logo.png";
import home from "@/img/home.png";
import { Column, Row } from "../Components/Layout";
import Button from "../Components/Button";
import { motion, useAnimation } from 'framer-motion';
import useOnScreen from "../Hooks/OnScreenHook";
import classNames from "classnames";

function StoreButton({ src, store, phrase, url }: { src: string, store: string, phrase: string, url: string }) {
    return (
        <Button to={url}>
            <Row gap="5px">
                <img src={src} alt={store} height={31} />
                <Column>
                    <span>{phrase}</span>
                    <span>{store}</span>
                </Column>
            </Row>
        </Button>
    );
}

function FeatureCard({ title, subtitle, side }: {
    title: React.ReactNode,
    subtitle: React.ReactNode,
    side?: 'left' | 'right'
}) {
    const controls = useAnimation();
    const rootRef = useRef<HTMLDivElement>(null);
    const onScreen = useOnScreen(rootRef);

    useEffect(() => {
        if (onScreen) {
            controls.start({
                x: 0,
                opacity: 1,
                transition: {
                    duration: 0.5,
                    ease: "easeOut"
                }
            });
        }
    }, [onScreen, controls]);


    return (
        <Column className={classNames("card", "feature", side)} crossAxisAlignment="stretch">
            <motion.div
                ref={rootRef}
                style={{ flex: 1, display: 'flex', flexDirection: 'column', justifyContent: 'center' }}
                initial={{ opacity: 0, x: side === 'left' ? -500 : 500 }}
                animate={controls}
            >
                <Row mainAxisAlignment="space-evenly" crossAxisAlignment="center" flex={1}>
                    {side == 'left' && (
                        <Row flex={1} mainAxisAlignment="center">
                            <img className="screenshot" src={home} />
                        </Row>
                    )}
                    <Column crossAxisAlignment="center" flex={1} gap="20px">
                        <span className="title">{title}</span>
                        <span className="subtitle">{subtitle}</span>
                    </Column>
                    {side == 'right' && (
                        <Row flex={1} mainAxisAlignment="center">
                            <img className="screenshot" src={home} />
                        </Row>
                    )}
                </Row>
            </motion.div>
        </Column>
    )
}

export default function Home() {
    return (
        <Column className="home" gap="50px">
            <div className="card">
                <Column mainAxisAlignment="space-around" style={{ flex: 1 }}>
                    <Column crossAxisAlignment="center">
                        <img src={dodo} alt="dodo" height={200} width={200} />
                        <h1>DODO</h1>
                        <h3>PRIVACY-FOCUSED BILL SPLITTING</h3>
                    </Column>
                    <Row mainAxisAlignment="space-around" gap="10px">
                        <StoreButton src={play} store="Google Play" phrase="Get it on" url={'a'} />
                        <StoreButton src={appStore} store="App Store" phrase="Download on the" url={'a'} />
                        <StoreButton src={microsoft} store="Microsoft Store" phrase="Get it from the" url={'a'} />
                        <StoreButton src={dodo} store="Online" phrase="Use it" url={'a'} />
                    </Row>
                </Column>
            </div>
            <FeatureCard
                title="Streamlined bill-splitting"
                subtitle="Experience modern, intuitive bill splitting with Dodo. The all-in-one app for easy, customizable expense sharing."
                side="left"
            />
            <FeatureCard
                title="Expense tracking made easy"
                subtitle="Deal with complicated expenses, calculate on the fly, categorize your purchases, and pick your currency effortlessly. All in one place with Dodo."
                side="right"
            />
        </Column>
    )
}