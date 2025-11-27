/**
 * Header Component
 * Screen header with title, back button, and actions
 */

import React from 'react';
import { View, StyleSheet, TouchableOpacity, Platform } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { useTheme } from '../../context/ThemeContext';
import { Layout } from '../../constants/Layout';
import { ThemedText } from './ThemedText';

interface HeaderProps {
  title: string;
  subtitle?: string;
  showBack?: boolean;
  onBackPress?: () => void;
  rightAction?: React.ReactNode;
  transparent?: boolean;
  large?: boolean;
}

export function Header({
  title,
  subtitle,
  showBack = false,
  onBackPress,
  rightAction,
  transparent = false,
  large = false,
}: HeaderProps) {
  const { colors } = useTheme();
  const insets = useSafeAreaInsets();
  const router = useRouter();

  const handleBack = () => {
    if (onBackPress) {
      onBackPress();
    } else if (router.canGoBack()) {
      router.back();
    }
  };

  return (
    <View
      style={[
        styles.container,
        {
          paddingTop: insets.top,
          backgroundColor: transparent ? 'transparent' : colors.background,
          borderBottomColor: transparent ? 'transparent' : colors.separator,
        },
      ]}
    >
      <View style={styles.content}>
        <View style={styles.left}>
          {showBack && (
            <TouchableOpacity
              onPress={handleBack}
              style={styles.backButton}
              hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
            >
              <Ionicons
                name="chevron-back"
                size={28}
                color={colors.primary}
              />
            </TouchableOpacity>
          )}
        </View>

        <View style={styles.center}>
          {!large && (
            <>
              <ThemedText variant="headline" numberOfLines={1}>
                {title}
              </ThemedText>
              {subtitle && (
                <ThemedText
                  variant="caption1"
                  color="secondary"
                  numberOfLines={1}
                >
                  {subtitle}
                </ThemedText>
              )}
            </>
          )}
        </View>

        <View style={styles.right}>{rightAction}</View>
      </View>

      {large && (
        <View style={styles.largeTitle}>
          <ThemedText variant="largeTitle">{title}</ThemedText>
          {subtitle && (
            <ThemedText variant="subheadline" color="secondary">
              {subtitle}
            </ThemedText>
          )}
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  content: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    height: Layout.component.headerHeight,
    paddingHorizontal: Layout.spacing.sm,
  },
  left: {
    flex: 1,
    alignItems: 'flex-start',
  },
  center: {
    flex: 3,
    alignItems: 'center',
  },
  right: {
    flex: 1,
    alignItems: 'flex-end',
  },
  backButton: {
    padding: Layout.spacing.xs,
  },
  largeTitle: {
    paddingHorizontal: Layout.padding.horizontal,
    paddingBottom: Layout.spacing.sm,
  },
});

export default Header;
